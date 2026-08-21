#!/usr/bin/env sh
set -eu

npm ci
npm run verify:structure
npm run verify:isolation
npm run verify:lockfile
npm run typecheck
npm run lint
npm run test:coverage
npm run build
npm run verify:preview
npx playwright install
npm run test:e2e
npm run test:a11y

echo "Local frontend verification completed without GitHub Actions."
