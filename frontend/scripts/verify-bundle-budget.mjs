import { readFile, readdir } from 'node:fs/promises';
import { gzipSync } from 'node:zlib';
import { join, relative, resolve, sep } from 'node:path';

const G1_INITIAL_ROUTE_GZIP_BYTES = 510_780;
const measureOnly = process.argv.includes('--measure');
const frontendRoot = resolve(process.env.FREEITSM_BUNDLE_FRONTEND_ROOT ?? join(import.meta.dirname, '..'));
const distDir = join(frontendRoot, 'dist');
const manifestPath = join(distDir, '.vite', 'manifest.json');
const budgetPath = resolve(
  process.env.FREEITSM_BUNDLE_BUDGET_CONFIG ?? join(frontendRoot, 'bundle-budget.json')
);

function normalizePath(path) {
  return path.split(sep).join('/');
}

async function walkJavaScript(directory) {
  const files = [];
  for (const entry of await readdir(directory, { withFileTypes: true })) {
    const absolutePath = join(directory, entry.name);
    if (entry.isDirectory()) files.push(...await walkJavaScript(absolutePath));
    else if (entry.isFile() && entry.name.endsWith('.js')) files.push(absolutePath);
  }
  return files;
}

function assertManifestChunk(manifest, key) {
  const chunk = manifest[key];
  if (!chunk || typeof chunk.file !== 'string' || !chunk.file.endsWith('.js')) {
    throw new Error(`Manifest key does not reference a JavaScript chunk: ${key}`);
  }
  return chunk;
}

function staticClosure(manifest, startKeys) {
  const visited = new Set();
  const visit = (key) => {
    if (visited.has(key)) return;
    const chunk = assertManifestChunk(manifest, key);
    visited.add(key);
    for (const dependencyKey of chunk.imports ?? []) visit(dependencyKey);
  };
  for (const key of startKeys) visit(key);
  return visited;
}

function dynamicImportsFrom(manifest, staticKeys) {
  const dynamicKeys = new Set();
  for (const key of staticKeys) {
    const chunk = assertManifestChunk(manifest, key);
    for (const dynamicKey of chunk.dynamicImports ?? []) dynamicKeys.add(dynamicKey);
  }
  return dynamicKeys;
}

function manifestKeyForSource(manifest, source) {
  return Object.keys(manifest).find((key) => {
    const normalized = normalizePath(key);
    return normalized === source || normalized.endsWith(`/${source}`);
  });
}

function filesForKeys(manifest, keys) {
  const files = new Set();
  for (const key of keys) files.add(assertManifestChunk(manifest, key).file);
  return files;
}

function totalMetrics(files, metricByFile) {
  let rawBytes = 0;
  let gzipBytes = 0;
  for (const file of files) {
    const metric = metricByFile.get(file);
    if (!metric) throw new Error(`Manifest references a missing JavaScript asset: ${file}`);
    rawBytes += metric.rawBytes;
    gzipBytes += metric.gzipBytes;
  }
  return { rawBytes, gzipBytes };
}

function formatMetrics(label, metrics) {
  console.log(`${label} raw bytes: ${metrics.rawBytes}`);
  console.log(`${label} gzip bytes: ${metrics.gzipBytes}`);
}

const budget = JSON.parse(await readFile(budgetPath, 'utf8'));
if (budget?.version !== 1) throw new Error('bundle-budget.json version must be 1');
if (typeof budget.defaultRouteSource !== 'string' || !budget.defaultRouteSource) {
  throw new Error('bundle-budget.json defaultRouteSource must be a non-empty string');
}
if (!Array.isArray(budget.requiredDynamicRouteSources) || budget.requiredDynamicRouteSources.length === 0) {
  throw new Error('bundle-budget.json requiredDynamicRouteSources must be a non-empty array');
}
const requiredSources = new Set();
for (const source of budget.requiredDynamicRouteSources) {
  if (typeof source !== 'string' || source.trim() === '') {
    throw new Error('Every requiredDynamicRouteSources entry must be a non-empty string');
  }
  if (requiredSources.has(source)) throw new Error(`Duplicate required route source: ${source}`);
  requiredSources.add(source);
}
if (!requiredSources.has(budget.defaultRouteSource)) {
  throw new Error('defaultRouteSource must also appear in requiredDynamicRouteSources');
}
if (!Number.isInteger(budget.minimumDynamicRouteEntries) || budget.minimumDynamicRouteEntries < requiredSources.size) {
  throw new Error('minimumDynamicRouteEntries must be an integer at least as large as requiredDynamicRouteSources');
}
if (budget.forwardInitialRouteGzipBytes !== null &&
    (!Number.isInteger(budget.forwardInitialRouteGzipBytes) ||
      budget.forwardInitialRouteGzipBytes >= G1_INITIAL_ROUTE_GZIP_BYTES)) {
  throw new Error(`forwardInitialRouteGzipBytes must be null or an integer below ${G1_INITIAL_ROUTE_GZIP_BYTES}`);
}

const manifest = JSON.parse(await readFile(manifestPath, 'utf8'));
const entries = Object.entries(manifest).filter(([, chunk]) => chunk?.isEntry === true);
if (entries.length !== 1) throw new Error(`Expected exactly one Vite entry chunk, found ${entries.length}`);
const [entryKey, entryChunk] = entries[0];
assertManifestChunk(manifest, entryKey);

const metricByFile = new Map();
for (const absolutePath of await walkJavaScript(distDir)) {
  const file = normalizePath(relative(distDir, absolutePath));
  const bytes = await readFile(absolutePath);
  metricByFile.set(file, {
    file,
    rawBytes: bytes.byteLength,
    // Match Vite 7's build reporter, which calls node:zlib gzip with default options.
    gzipBytes: gzipSync(bytes).byteLength,
    entry: file === entryChunk.file
  });
}

const shellKeys = staticClosure(manifest, [entryKey]);
const reachableDynamicKeys = dynamicImportsFrom(manifest, shellKeys);
const requiredRouteKeys = new Map();
for (const source of requiredSources) {
  const key = manifestKeyForSource(manifest, source);
  if (!key) throw new Error(`Required route source is missing from the manifest: ${source}`);
  if (manifest[key]?.isDynamicEntry !== true || !reachableDynamicKeys.has(key) || shellKeys.has(key)) {
    throw new Error(`Required route must remain a dynamic import outside the startup closure: ${source}`);
  }
  requiredRouteKeys.set(source, key);
}
const defaultRouteKey = requiredRouteKeys.get(budget.defaultRouteSource);

const defaultRouteKeys = staticClosure(manifest, [defaultRouteKey]);
const initialRouteKeys = new Set([...shellKeys, ...defaultRouteKeys]);
const entryMetrics = totalMetrics(new Set([entryChunk.file]), metricByFile);
const shellMetrics = totalMetrics(filesForKeys(manifest, shellKeys), metricByFile);
const defaultRouteMetrics = totalMetrics(filesForKeys(manifest, defaultRouteKeys), metricByFile);
const initialRouteMetrics = totalMetrics(filesForKeys(manifest, initialRouteKeys), metricByFile);
const improvementBytes = G1_INITIAL_ROUTE_GZIP_BYTES - initialRouteMetrics.gzipBytes;
const improvementPercent = improvementBytes / G1_INITIAL_ROUTE_GZIP_BYTES * 100;

console.log(`Bundle entry: ${entryChunk.file}`);
formatMetrics('Entry', entryMetrics);
formatMetrics('Synchronous AppShell closure', shellMetrics);
formatMetrics('Home/default route closure', defaultRouteMetrics);
formatMetrics('Actual /ui/ initial JS transfer', initialRouteMetrics);
console.log(`Previous G1 baseline gzip bytes: ${G1_INITIAL_ROUTE_GZIP_BYTES}`);
console.log(`Improvement bytes: ${improvementBytes}`);
console.log(`Improvement percent: ${improvementPercent.toFixed(2)}%`);
console.log(`Reachable dynamic entries: ${reachableDynamicKeys.size}`);
console.log(`Forward initial-route gzip budget: ${budget.forwardInitialRouteGzipBytes ?? 'NOT_SET'}`);
console.log('Required lazy route entries:');
for (const [source, key] of requiredRouteKeys) console.log(`  ${source} -> ${manifest[key].file}`);

const largestChunks = [...metricByFile.values()]
  .sort((left, right) => right.gzipBytes - left.gzipBytes)
  .slice(0, 10);
console.log('Largest JavaScript chunks:');
for (const chunk of largestChunks) {
  console.log(
    `  ${chunk.entry ? '[entry] ' : '        '}${chunk.file}: ` +
    `${chunk.rawBytes} raw / ${chunk.gzipBytes} gzip bytes`
  );
}

if (initialRouteMetrics.gzipBytes >= G1_INITIAL_ROUTE_GZIP_BYTES) {
  throw new Error(
    `Initial /ui/ route did not improve: ${initialRouteMetrics.gzipBytes} >= ${G1_INITIAL_ROUTE_GZIP_BYTES}`
  );
}
if (reachableDynamicKeys.size < budget.minimumDynamicRouteEntries) {
  throw new Error(
    `Expected at least ${budget.minimumDynamicRouteEntries} reachable dynamic entries, ` +
    `found ${reachableDynamicKeys.size}`
  );
}
if (budget.forwardInitialRouteGzipBytes === null) {
  if (!measureOnly) {
    throw new Error(
      'Forward bundle budget is not set. Run npm run measure:bundle, record the real result, ' +
      'set bundle-budget.json from that evidence, and rerun npm run verify:bundle-budget.'
    );
  }
} else if (initialRouteMetrics.gzipBytes > budget.forwardInitialRouteGzipBytes) {
  throw new Error(
    `Initial /ui/ route exceeds forward budget: ${initialRouteMetrics.gzipBytes} > ` +
    `${budget.forwardInitialRouteGzipBytes}`
  );
}
