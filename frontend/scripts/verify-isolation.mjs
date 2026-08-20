import { readdir, readFile } from 'node:fs/promises';
import { join } from 'node:path';

async function walk(directory) {
  const result = [];
  for (const entry of await readdir(directory, { withFileTypes: true })) {
    if (['node_modules', 'dist', 'coverage', '.git'].includes(entry.name)) continue;
    const path = join(directory, entry.name);
    if (entry.isDirectory()) result.push(...(await walk(path)));
    else result.push(path);
  }
  return result;
}

const files = await walk('.');
const forbidden = files.filter((path) => path.endsWith('.php'));
if (forbidden.length) throw new Error(`PHP files found in frontend: ${forbidden.join(', ')}`);
const vite = await readFile('vite.config.ts', 'utf8');
if (!vite.includes("outDir: 'dist'")) throw new Error('Build output is not frontend/dist');
if (/\.\.\/.*(?:api|includes|tickets|asset-management)/.test(vite)) {
  throw new Error('Vite configuration reaches into legacy PHP source paths');
}
const featureEntries = await readdir('src/features');
if (featureEntries.some((entry) => entry !== 'README.md')) {
  throw new Error('Business feature migration started before G1');
}
console.log('Isolation check passed: no PHP, BFF, or business feature code in frontend.');
