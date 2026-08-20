import { access } from 'node:fs/promises';
import { constants } from 'node:fs';

try {
  await access('package-lock.json', constants.R_OK);
} catch {
  console.error('package-lock.json is missing; see DEPENDENCY_INSTALL_BLOCKER.md');
  process.exit(1);
}
console.log('Lockfile exists. Run npm ci before accepting it.');
