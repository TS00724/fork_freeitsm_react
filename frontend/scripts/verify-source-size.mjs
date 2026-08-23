import { readFile, readdir } from 'node:fs/promises';
import { basename, extname, join, relative, resolve, sep } from 'node:path';

const frontendRoot = resolve(import.meta.dirname, '..');
const repositoryRoot = resolve(process.env.FREEITSM_SOURCE_AUDIT_ROOT ?? join(frontendRoot, '..'));
const exceptionsPath = resolve(
  process.env.FREEITSM_SOURCE_AUDIT_EXCEPTIONS ?? join(frontendRoot, 'source-size-exceptions.json')
);

const ignoredDirectoryNames = new Set([
  '.git',
  'node_modules',
  'dist',
  'coverage',
  'playwright-report',
  'test-results',
  '.vite'
]);
const scanRoots = [
  'frontend/src',
  'frontend/scripts',
  'frontend/tests',
  'frontend/e2e',
  'api/ui/v1'
];

const policies = {
  orchestration: { label: 'router/provider/AppShell', review: 150, hard: 250 },
  react: { label: 'React TSX', review: 200, hard: 300 },
  typescript: { label: 'TypeScript', review: 250, hard: 400 },
  node: { label: 'Node script', review: 250, hard: 400 },
  php: { label: 'UI-BFF PHP', review: 250, hard: 400 },
  test: { label: 'test', review: 300, hard: 500 }
};

function normalizePath(path) {
  return path.split(sep).join('/');
}

function countPhysicalLines(source) {
  if (source.length === 0) return 0;
  const lines = source.replaceAll('\r\n', '\n').replaceAll('\r', '\n').split('\n');
  if (lines.at(-1) === '') lines.pop();
  return lines.length;
}

function isGenerated(path) {
  return path.startsWith('frontend/src/api/generated/');
}

function isTest(path) {
  return path.startsWith('frontend/tests/') ||
    path.startsWith('frontend/e2e/') ||
    path.startsWith('api/ui/v1/tests/') ||
    /\.(?:test|spec)\.[cm]?[jt]sx?$/.test(path);
}

function isOrchestration(path) {
  const name = basename(path);
  return path === 'frontend/src/app/router.tsx' ||
    path === 'frontend/src/layouts/AppShell.tsx' ||
    path.includes('/providers/') ||
    /(?:Provider|Providers|Boundary)\.tsx$/.test(name);
}

function policyFor(path) {
  if (isTest(path)) return policies.test;
  if (isOrchestration(path)) return policies.orchestration;
  switch (extname(path)) {
    case '.tsx': return policies.react;
    case '.ts': return policies.typescript;
    case '.mjs': return policies.node;
    case '.php': return path.startsWith('api/ui/v1/') ? policies.php : null;
    default: return null;
  }
}

async function walk(directory) {
  let entries;
  try {
    entries = await readdir(directory, { withFileTypes: true });
  } catch (error) {
    if (error?.code === 'ENOENT') return [];
    throw error;
  }

  const files = [];
  for (const entry of entries) {
    if (entry.isDirectory() && ignoredDirectoryNames.has(entry.name)) continue;
    const absolutePath = join(directory, entry.name);
    if (entry.isDirectory()) files.push(...await walk(absolutePath));
    else if (entry.isFile()) files.push(absolutePath);
  }
  return files;
}

async function loadExceptions() {
  const document = JSON.parse(await readFile(exceptionsPath, 'utf8'));
  if (document?.version !== 1 || !document.exceptions || Array.isArray(document.exceptions)) {
    throw new Error('source-size-exceptions.json must contain { version: 1, exceptions: {} }');
  }
  return document.exceptions;
}

function validateException(path, exception, policy) {
  if (!exception || !Number.isInteger(exception.maxLoc) || exception.maxLoc <= policy.hard) {
    throw new Error(`${path}: exception maxLoc must be an integer above the normal hard target ${policy.hard}`);
  }
  if (typeof exception.reason !== 'string' || exception.reason.trim().length < 20) {
    throw new Error(`${path}: exception reason must contain at least 20 non-whitespace characters`);
  }
}

const exceptions = await loadExceptions();
const rows = [];
for (const root of scanRoots) {
  const absoluteRoot = join(repositoryRoot, root);
  for (const absolutePath of await walk(absoluteRoot)) {
    const path = normalizePath(relative(repositoryRoot, absolutePath));
    if (isGenerated(path)) continue;
    const policy = policyFor(path);
    if (!policy) continue;
    const source = await readFile(absolutePath, 'utf8');
    const loc = countPhysicalLines(source);
    const exception = exceptions[path];
    if (exception) validateException(path, exception, policy);

    let status = 'PASS';
    let allowedHard = policy.hard;
    if (loc > policy.hard) {
      if (exception && loc <= exception.maxLoc) {
        status = 'EXCEPTION';
        allowedHard = exception.maxLoc;
      } else {
        status = 'FAIL';
      }
    } else if (loc > policy.review) {
      status = 'REVIEW';
    } else if (exception) {
      throw new Error(`${path}: stale exception is no longer needed (${loc} <= ${policy.hard})`);
    }

    rows.push({ path, loc, policy, allowedHard, status });
  }
}

const scannedPaths = new Set(rows.map((row) => row.path));
for (const path of Object.keys(exceptions)) {
  if (!scannedPaths.has(path)) throw new Error(`${path}: exception does not match a scanned source file`);
}

rows.sort((left, right) => right.loc - left.loc || left.path.localeCompare(right.path));
console.log('STATUS     LOC  REVIEW  HARD  POLICY                     PATH');
for (const row of rows) {
  console.log(
    `${row.status.padEnd(9)} ${String(row.loc).padStart(4)}  ${String(row.policy.review).padStart(6)}  ` +
    `${String(row.allowedHard).padStart(4)}  ${row.policy.label.padEnd(25)} ${row.path}`
  );
}

const failures = rows.filter((row) => row.status === 'FAIL');
const reviews = rows.filter((row) => row.status === 'REVIEW');
const exceptionRows = rows.filter((row) => row.status === 'EXCEPTION');
console.log(
  `Source-size summary: ${rows.length} files; ${reviews.length} review; ` +
  `${exceptionRows.length} exceptions; ${failures.length} failures.`
);
console.log('Responsibility mixing still requires human review below numeric thresholds.');

if (failures.length > 0) {
  throw new Error(`${failures.length} source file(s) exceed the hard LOC target without an approved exception`);
}
