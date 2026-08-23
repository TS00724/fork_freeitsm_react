# ADJ-001 — source-size, responsibility and bundle governance

Status: **Queued before WP-05**  
Type: adjustment / technical-debt control  
Base after governance merge: read current `main` before execution  
Candidate implementation branch to inspect, not trust blindly: `perf-main-chunk-lazy-split`

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

## Scope

### A. Source responsibility / LOC gate

Implement a dependency-free source audit, preferably under `frontend/scripts/`, that checks new/materially changed React/UI-BFF code against the repository thresholds in `AGENTS.md`.

Minimum behavior:

- report file path and physical LOC;
- distinguish review threshold from hard target;
- exclude generated code, `node_modules`, `dist`, coverage and untouched legacy PHP;
- fail verification when a hard target is exceeded without an explicit allowlisted exception/reason;
- keep exception policy narrow and reviewable;
- do not reward mechanical file slicing; reviewer audit must still check responsibility boundaries.

Integrate the gate into the repository's normal local verification path (`npm run verify` or a clearly documented equivalent for mixed PHP/frontend changes).

### B. Bundle/initial-route gate

Take the existing `perf-main-chunk-lazy-split` branch only as candidate work. Re-read/diff it against current `main`; do not merge it blindly.

The final implementation must:

- lazy-load foundation/business routes where appropriate;
- keep heavyweight feature-only dependencies out of startup-critical AppShell code;
- avoid using `manualChunks` alone as proof of improvement;
- emit Vite manifest evidence;
- calculate gzip bytes for the actual `/ui/` initial-route dependency closure (entry + static imports + default-route lazy chunk/dependencies, de-duplicated);
- keep the historical 510,780-byte gzip value visible as the G1 baseline;
- fail if the initial-route transfer does not measurably improve over that baseline;
- report per-chunk raw/gzip sizes so later tuning is evidence-based.

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

## Acceptance evidence

Run in an environment that can materialize the exact repository and dependencies:

```bash
cd frontend
npm ci
npm run verify
```

The verification record must include:

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

If any required command cannot run, status remains `Blocked` or `Not verified`; do not merge the candidate performance branch merely from static inspection.

## Completion / stop rule

When ADJ-001 is verified:

1. update `progress/WORK_PROGRESS.md`, `progress/VERIFICATION_REPORT.md` and `progress/DECISION_LOG.md`;
2. create `handoffs/ADJ-001.md` with command evidence and measured before/after bundle data;
3. non-force fast-forward `main` only after re-reading the expected remote base;
4. stop again before WP-05 so the user can review the measured bundle result and source-governance policy.
