import { access, readFile } from 'node:fs/promises';
import { constants } from 'node:fs';

const required = [
  'package.json', 'index.html', 'vite.config.ts', 'tsconfig.json',
  'tsconfig.app.json', 'src/main.tsx', 'src/app/App.tsx',
  'src/app/router.tsx', 'src/app/providers/AppProviders.tsx',
  'src/config/runtimeConfig.ts', 'src/api/client.ts',
  'src/layouts/AppShell.tsx', 'tests/runtimeConfig.test.ts', 'tests/App.test.tsx',
  'scripts/serve-dist.mjs', 'scripts/verify-preview.mjs'
];
for (const path of required) await access(path, constants.R_OK);
const pkg = JSON.parse(await readFile('package.json', 'utf8'));
if (pkg.dependencies?.react !== '18.3.1') throw new Error('React 18.3.1 is not pinned');
if (pkg.dependencies?.['@elastic/eui'] !== '119.0.0') throw new Error('EUI 119.0.0 is not pinned');
if (!pkg.scripts?.typecheck || !pkg.scripts?.lint || !pkg.scripts?.test || !pkg.scripts?.build) {
  throw new Error('Required local validation scripts are missing');
}
console.log(`Structure check passed (${required.length} required files).`);
