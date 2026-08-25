# ADJ-001 — source-size, responsibility and bundle governance

Status: **Ready for Runtime Verification; not mergeable**  
Type: adjustment / technical-debt control  
Base `main`: `d7385470fb216cf504aac53667c43e7accf31675`  
Working branch: `adj-001-source-size-bundle-governance`  
Candidate inspected, not merged: `perf-main-chunk-lazy-split`

## Why this task exists

G1 accepted the React/EUI foundation while retaining a visible startup-bundle debt:

- 1,641.07 kB minified main chunk;
- 510.78 kB gzip main chunk.

G1 also made lazy route/feature/component loading mandatory. Subsequent review identified a second maintainability risk: central files can grow indefinitely even if the output is split. The repository therefore needs both bundle budgets and responsibility/source-size governance before more platform/security code accumulates.

## Required guiding/audit sources

Every implementation/review pass for this task must read and use:

1. `AGENTS.md`
2. `skills/universal-code-writing/SKILL.md`
3. `skills/universal-code-writing/references/language-profiles.md`
4. `skills/universal-code-writing/references/review-checklist.md`
5. `progress/WORK_PROGRESS.md`
6. this task file

The universal skill supplies the implementation workflow; the review checklist supplies the final audit dimensions. FreeITSM-specific numeric limits and migration rules live in `AGENTS.md`.

## Current execution state

PHASE A is complete on the working branch:

- dependency-free source-size gate implemented;
- foundation routes converted to explicit lazy entries;
- default Home route no longer imports `EuiCodeBlock`/Prism;
- Vite manifest and actual initial-route closure analyzer implemented;
- all five foundation source paths must be reachable dynamic entries;
- forward budget remains `null` until a real production measurement exists;
- multi-responsibility WP-04 `Http.php` and `Router.php` were split into focused sibling classes while retaining compatibility loaders;
- no LOC exception is used;
- local PHP lint and the existing 36-test contract/security-negative runner passed;
- synthetic source-size negative and bundle-manifest accounting checks behaved as expected;
- `handoffs/ADJ-001.md` and `progress/verification/ADJ-001-source-size-bundle-results.md` record the evidence.

PHASE B remains blocked because the command environment cannot resolve `github.com` or `registry.npmjs.org`, has an effectively empty npm cache and has no trusted project `node_modules`. A real `npm ci`, Vite build, gzip measurement and measured forward budget remain mandatory.

## Scope

### A. Source responsibility / LOC gate

The dependency-free audit under `frontend/scripts/verify-source-size.mjs` checks new/materially changed React/UI-BFF code against the repository thresholds in `AGENTS.md`.

Required behavior:

- report file path and physical LOC;
- distinguish review threshold from hard target;
- exclude generated code, `node_modules`, `dist`, coverage and untouched legacy PHP;
- fail verification when a hard target is exceeded without an explicit allowlisted exception/reason;
- keep exception policy narrow and reviewable;
- do not reward mechanical file slicing; reviewer audit must still check responsibility boundaries.

The gate is integrated into the repository's normal local frontend verification path.

### B. Bundle/initial-route gate

The final implementation must:

- lazy-load foundation/business routes where appropriate;
- keep heavyweight feature-only dependencies out of startup-critical AppShell code;
- avoid using `manualChunks` alone as proof of improvement;
- emit Vite manifest evidence;
- calculate gzip bytes for the actual `/ui/` initial-route dependency closure (entry + static imports + default-route lazy chunk/dependencies, de-duplicated);
- keep the historical 510,780-byte gzip value visible as the G1 baseline;
- fail if the initial-route transfer does not measurably improve over that baseline;
- report per-chunk raw/gzip sizes so later tuning is evidence-based;
- require Home, Architecture, 403, generic Error and 404 source modules to remain explicit lazy entries.

After the first real build, set a tighter forward budget only from measured evidence; do not invent a target that the build has never demonstrated.

### C. Regression coverage

- exercise all foundation lazy routes in component tests;
- keep existing coverage thresholds; do not lower coverage to make splitting pass;
- preserve `/ui/`, theme, 403, error and 404 behavior;
- keep Playwright/axe debt labelled deferred unless it is actually run.

## Explicit non-scope

This adjustment task must not start:

- WP-05 Session/CSRF/tenant/RBAC/object-scope implementation;
- Calendar, Watchtower, Tickets or any business feature;
- production Apache `/ui/*` fallback;
- Go/go-zero, clustering or SOC runtime;
- GitHub Actions or a Pull Request.

## Acceptance evidence still required

Run in an environment that can materialize the exact repository and dependencies:

```bash
cd frontend
npm ci
npm run verify:source-size
npm run typecheck
npm run lint
npm run test
npm run test:coverage
npm run build
npm run measure:bundle
```

If the actual `/ui/` gzip result is below 510,780 bytes, set a forward budget from that measured value plus limited headroom, then rerun:

```bash
npm run verify:bundle-budget
npm run verify
```

Also run:

```bash
find api/ui/v1 -name '*.php' -print0 | xargs -0 -n1 php -l
php api/ui/v1/tests/run.php
git diff --check
```

The final verification record must include:

- exact starting and ending SHA;
- Node/npm versions;
- source-size audit output;
- production chunk raw/gzip table;
- `/ui/` actual initial-route gzip total;
- comparison with 510,780-byte G1 baseline;
- route/component tests and coverage result;
- any exceptions with reasons;
- `git diff --check` or repository equivalent;
- review-checklist audit summary.

If any required command cannot run, status remains `Blocked` or `Not verified`; do not merge the branch merely from static inspection.

## Completion / stop rule

When ADJ-001 is verified:

1. update `progress/WORK_PROGRESS.md`, `progress/VERIFICATION_REPORT.md` and `progress/DECISION_LOG.md`;
2. finalize `handoffs/ADJ-001.md` with command evidence and measured before/after bundle data;
3. non-force fast-forward `main` only after re-reading the expected remote base;
4. stop again before WP-05 so the user can review the measured bundle result and source-governance policy.
