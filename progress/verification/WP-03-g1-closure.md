# WP-03 G1 verification-closure evidence

- Date: 2026-08-21 (Asia/Taipei)
- Repository: `TS00724/fork_freeitsm_react`
- Working branch: `agent/g1-verification-closure`
- Start SHA: `d78be7534b36b7b596a1e5ddc1bbedd4b3640b16`
- Verified source SHA: `46c901597557abe7f319a880c9a3539105307196`
- Runtime: Node `v24.19.0`, npm `11.9.0`

## Successful commands

All frontend commands ran from `frontend/`.

| Command | Exit | Recorded result |
|---|---:|---|
| `npm install --package-lock-only --ignore-scripts` | 0 | Real npm v3 lockfile generated |
| `npm ci` | 0 | Clean install completed; 464 packages added |
| `npm audit --package-lock-only --omit=dev --json` | 0 | 0 production vulnerabilities reported |
| `npm audit --package-lock-only --json` | 0 | 0 total vulnerabilities reported across 514 resolved dependency entries |
| `npm run verify` (final) | 0 | Structure, isolation, lockfile, typecheck, lint, coverage, build, and preview probe passed |
| `npm run typecheck` (within final verify) | 0 | Strict project references, including Playwright E2E TypeScript, passed |
| `npm run lint` (within final verify) | 0 | ESLint completed with zero warnings/errors |
| `npm run test:coverage` (within final verify) | 0 | 3 files / 43 tests passed |
| `npm run build` (within final verify) | 0 | 2,730 modules transformed; production build emitted without sourcemaps |
| `npm run verify:preview` (within final verify) | 0 | `/ui/` shell/deep links returned HTML, built asset returned JavaScript, missing asset/root returned 404 |
| `git diff --check` | 0 | No whitespace errors before source commit |

Coverage from the final test run:

| Metric | Result | Threshold |
|---|---:|---:|
| Statements | 84.01% | 80% |
| Branches | 78.53% | 75% |
| Functions | 84.84% | 80% |
| Lines | 87.83% | 80% |

The build emitted a non-fatal size warning: the main minified JavaScript chunk
was 1,641.07 kB (510.78 kB gzip). This remains a performance/code-splitting risk
for the user's Human QoS review; the warning was not hidden by raising Vite's
limit.

## Corrected failures retained as evidence

| Command | Exit | Cause | Correction |
|---|---:|---|---|
| First `npm run verify` | 2 | ESLint typed rules were applied to `.mjs` verification scripts | Applied `disableTypeChecked` only to JavaScript scripts; retained typed lint for TS/TSX |
| First full lint after that correction | 1 | Promise-returning navigation callback and unused placeholder parameter | Converted callback to explicit void return and removed the unused parameter |
| Initial HTTP probe against Vite preview | probe failure | `/ui/assets/*` returned the SPA HTML instead of JavaScript | Added isolated `preview:test` static mount plus an automated MIME/deep-link probe |

## Browser-level blocker

| Command | Exit | Classification | Evidence |
|---|---:|---|---|
| `npx playwright install` | 1 | External environment blocker | Official CDN returned truncated/non-ZIP bodies and then HTTP 502 with proxy certificate-time error |
| Playwright install using the documented alternate download host | 1 | External environment blocker | Gateway returned HTTP 400 |
| `npm run test:e2e` | 1 | Blocked before browser assertions | Production build/server started, but all 18 Chromium/Firefox/WebKit cases stopped at `browserType.launch` because executables were absent |

The 18 cases are not reported as passed. They are also not treated as product
assertion failures because no page was launched. The axe checks are part of
those cases and remain unexecuted at browser level.

## Scope and safety confirmation

- no `.github/workflows` file exists;
- no GitHub Actions workflow was created, edited, run, or depended upon;
- no pull request was created;
- only `origin=https://github.com/TS00724/fork_freeitsm_react.git` is configured;
- no upstream remote, force push, PHP/BFF handler, `.htaccess` change, or
  business feature was introduced;
- Go/go-zero and clustering remain future-only.

## Remaining closure items

1. Run Playwright/axe where the pinned browser binaries can be installed.
2. User performs Human QoS on `/ui/` and records `Pass`, `Needs tuning`, or
   `Block`.
3. Repository owner/legal records acceptance or rejection of the EUI/theme
   SSPL v1 / Elastic License 2.0 terms for the intended SOC deployment.
4. Apache `/ui/*` fallback remains a later narrowly reviewed server change; the
   local preview probe must not be cited as Apache evidence.
