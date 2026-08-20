#!/usr/bin/env sh
set -eu

npm ci
npm run verify:structure
npm run verify:isolation
npm run verify:lockfile
npm run typecheck
npm run lint
npm run test
npm run build

echo "Local frontend verification completed without GitHub Actions."
