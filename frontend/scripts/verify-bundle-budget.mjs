import { readFile, readdir } from 'node:fs/promises';
import { gzipSync } from 'node:zlib';
import { join, resolve } from 'node:path';

const frontendRoot = resolve(import.meta.dirname, '..');
const distDir = join(frontendRoot, 'dist');
const manifestPath = join(distDir, '.vite', 'manifest.json');

// The pre-splitting G1 build reported 510.78 kB gzip for the single main chunk.
// This gate intentionally requires a measurable reduction and prevents future
// synchronous business imports from silently returning to that baseline.
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

const entryBytes = await readFile(join(distDir, entry.file));
const entryRawBytes = entryBytes.byteLength;
const entryGzipBytes = gzipSync(entryBytes, { level: 9 }).byteLength;
const dynamicImports = Array.isArray(entry.dynamicImports) ? entry.dynamicImports : [];

const assetDir = join(distDir, 'assets');
const assetNames = (await readdir(assetDir)).filter((name) => name.endsWith('.js'));
const chunkRows = [];
for (const name of assetNames) {
  const bytes = await readFile(join(assetDir, name));
  chunkRows.push({
    file: `assets/${name}`,
    rawBytes: bytes.byteLength,
    gzipBytes: gzipSync(bytes, { level: 9 }).byteLength,
    entry: `assets/${name}` === entry.file
  });
}
chunkRows.sort((a, b) => b.gzipBytes - a.gzipBytes);

console.log(`Bundle entry: ${entry.file}`);
console.log(`Entry raw bytes: ${entryRawBytes}`);
console.log(`Entry gzip bytes: ${entryGzipBytes}`);
console.log(`Previous G1 main gzip bytes: ${LEGACY_MAIN_GZIP_BYTES}`);
console.log(`Improvement bytes: ${LEGACY_MAIN_GZIP_BYTES - entryGzipBytes}`);
console.log(`Dynamic imports from entry: ${dynamicImports.length}`);
console.log('JavaScript chunks (gzip bytes):');
for (const row of chunkRows) {
  console.log(`  ${row.entry ? '[entry] ' : '        '}${row.file}: ${row.gzipBytes}`);
}

if (entryGzipBytes >= LEGACY_MAIN_GZIP_BYTES) {
  throw new Error(
    `Startup bundle did not improve: ${entryGzipBytes} gzip bytes >= legacy ${LEGACY_MAIN_GZIP_BYTES}`
  );
}
if (entryGzipBytes > MAX_ENTRY_GZIP_BYTES) {
  throw new Error(
    `Startup bundle exceeds budget: ${entryGzipBytes} gzip bytes > ${MAX_ENTRY_GZIP_BYTES}`
  );
}
if (dynamicImports.length < MIN_DYNAMIC_CHUNKS) {
  throw new Error(
    `Expected at least ${MIN_DYNAMIC_CHUNKS} lazy route chunks, found ${dynamicImports.length}`
  );
}
