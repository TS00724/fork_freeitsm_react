import { readFile, readdir } from 'node:fs/promises';
import { gzipSync } from 'node:zlib';
import { join, resolve } from 'node:path';

const frontendRoot = resolve(import.meta.dirname, '..');
const distDir = join(frontendRoot, 'dist');
const manifestPath = join(distDir, '.vite', 'manifest.json');

// The pre-splitting G1 build reported 510.78 kB gzip for the single main chunk.
// Two budgets are enforced:
// 1) the synchronous entry must be smaller than the old main chunk; and
// 2) the real default-route transfer (entry/static deps + HomePage/static deps)
//    must also improve, so merely moving the same bytes into another file cannot
//    make this gate pass.
const LEGACY_MAIN_GZIP_BYTES = 510_780;
const MAX_ENTRY_GZIP_BYTES = 500_000;
const MIN_DYNAMIC_CHUNKS = 5;

const manifest = JSON.parse(await readFile(manifestPath, 'utf8'));
const entries = Object.entries(manifest).filter(([, value]) => value?.isEntry === true);
if (entries.length !== 1) {
  throw new Error(`Expected exactly one Vite entry chunk, found ${entries.length}`);
}

const [entryKey, entry] = entries[0];
if (typeof entry.file !== 'string' || !entry.file.endsWith('.js')) {
  throw new Error(`Entry ${entryKey} does not reference a JavaScript file`);
}

const assetDir = join(distDir, 'assets');
const assetNames = (await readdir(assetDir)).filter((name) => name.endsWith('.js'));
const chunkRows = [];
const gzipByFile = new Map();
for (const name of assetNames) {
  const file = `assets/${name}`;
  const bytes = await readFile(join(assetDir, name));
  const gzipBytes = gzipSync(bytes, { level: 9 }).byteLength;
  gzipByFile.set(file, gzipBytes);
  chunkRows.push({
    file,
    rawBytes: bytes.byteLength,
    gzipBytes,
    entry: file === entry.file
  });
}
chunkRows.sort((a, b) => b.gzipBytes - a.gzipBytes);

function staticClosure(startKey) {
  const visited = new Set();
  const visit = (key) => {
    if (visited.has(key)) return;
    const chunk = manifest[key];
    if (!chunk || typeof chunk.file !== 'string' || !chunk.file.endsWith('.js')) return;
    visited.add(key);
    for (const dependencyKey of chunk.imports ?? []) visit(dependencyKey);
  };
  visit(startKey);
  return visited;
}

function gzipTotal(keys) {
  let total = 0;
  for (const key of keys) {
    const file = manifest[key]?.file;
    if (typeof file !== 'string' || !file.endsWith('.js')) continue;
    const gzipBytes = gzipByFile.get(file);
    if (typeof gzipBytes !== 'number') {
      throw new Error(`Manifest references missing JavaScript asset: ${file}`);
    }
    total += gzipBytes;
  }
  return total;
}

const entryBytes = await readFile(join(distDir, entry.file));
const entryRawBytes = entryBytes.byteLength;
const entryGzipBytes = gzipByFile.get(entry.file);
if (typeof entryGzipBytes !== 'number') {
  throw new Error(`Entry asset is missing from dist/assets: ${entry.file}`);
}
const dynamicImports = Array.isArray(entry.dynamicImports) ? entry.dynamicImports : [];

const homeKey = Object.keys(manifest).find(
  (key) => key === 'src/pages/HomePage.tsx' || key.endsWith('/src/pages/HomePage.tsx')
);
if (!homeKey || manifest[homeKey]?.isDynamicEntry !== true) {
  throw new Error('HomePage must remain a Vite dynamic entry');
}

const entryClosure = staticClosure(entryKey);
const initialRouteClosure = new Set([...entryClosure, ...staticClosure(homeKey)]);
const shellTransferGzipBytes = gzipTotal(entryClosure);
const initialRouteGzipBytes = gzipTotal(initialRouteClosure);

console.log(`Bundle entry: ${entry.file}`);
console.log(`Entry raw bytes: ${entryRawBytes}`);
console.log(`Entry gzip bytes: ${entryGzipBytes}`);
console.log(`Synchronous shell transfer gzip bytes: ${shellTransferGzipBytes}`);
console.log(`Default /ui/ route transfer gzip bytes: ${initialRouteGzipBytes}`);
console.log(`Previous G1 single-main gzip bytes: ${LEGACY_MAIN_GZIP_BYTES}`);
console.log(`Default-route improvement bytes: ${LEGACY_MAIN_GZIP_BYTES - initialRouteGzipBytes}`);
console.log(`Dynamic imports from entry: ${dynamicImports.length}`);
console.log('JavaScript chunks (gzip bytes):');
for (const row of chunkRows) {
  console.log(`  ${row.entry ? '[entry] ' : '        '}${row.file}: ${row.gzipBytes}`);
}

if (entryGzipBytes > MAX_ENTRY_GZIP_BYTES) {
  throw new Error(
    `Synchronous entry exceeds budget: ${entryGzipBytes} gzip bytes > ${MAX_ENTRY_GZIP_BYTES}`
  );
}
if (initialRouteGzipBytes >= LEGACY_MAIN_GZIP_BYTES) {
  throw new Error(
    `Default route did not improve: ${initialRouteGzipBytes} gzip bytes >= legacy ${LEGACY_MAIN_GZIP_BYTES}`
  );
}
if (dynamicImports.length < MIN_DYNAMIC_CHUNKS) {
  throw new Error(
    `Expected at least ${MIN_DYNAMIC_CHUNKS} lazy route chunks, found ${dynamicImports.length}`
  );
}
