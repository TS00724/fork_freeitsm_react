# ADJ-001 — source-size, responsibility and bundle governance

Status: **Phase A owner-accepted; Phase B deferred before G2/production**  
Type: adjustment / technical-debt control  
Base `main`: `d7385470fb216cf504aac53667c43e7accf31675`  
Working branch: `adj-001-source-size-bundle-governance`  
Candidate inspected, not merged: `perf-main-chunk-lazy-split`

## Owner gate change — 2026-08-25

The project owner authorizes the completed Phase A implementation to be
integrated and authorizes WP-05 to start. Phase B is deferred, not waived and not
reported as Passed.

Phase B must complete before the earlier of:

1. G2 closure; or
2. approval of any React UI production release.

If the measured actual `/ui/` initial-route JavaScript is `>= 510780` gzip bytes,
ADJ-001 reopens as a blocking adjustment and measured follow-up splitting is
required.

## Why this task exists

G1 accepted the React/EUI foundation while retaining a visible startup-bundle debt:

- 1,641.07 kB minified main chunk;
- 510.78 kB gzip main chunk.

G1 also made lazy route/feature/component loading mandatory. Subsequent review
identified a second maintainability risk: central files can grow indefinitely
even if output is split. The repository therefore needs both bundle budgets and
responsibility/source-size governance.

## Required guiding/audit sources

Every implementation/review pass for this task must read and use:

1. `AGENTS.md`
2. `skills/universal-code-writing/SKILL.md`
3. `skills/universal-code-writing/references/language-profiles.md`
4. `skills/universal-code-writing/references/review-checklist.md`
5. `progress/WORK_PROGRESS.md`
6. this task file

## Phase A result

Completed implementation:

- dependency-free source-size gate;
- explicit lazy entries for Home, Architecture, 403, generic Error and 404;
- removal of default-route `EuiCodeBlock`/Prism source dependency;
- Vite manifest and de-duplicated actual initial-route closure analyzer;
- unresolved forward budget until a real production measurement;
- responsibility split of WP-04 `Http.php` and `Router.php` into focused sibling
  classes while retaining compatibility loaders;
- no source-size exception;
- local PHP lint and existing 36-test contract/security-negative runner passed;
- synthetic source-size negative and bundle-manifest accounting tests behaved as
  expected;
- handoff and verification evidence prepared.

## Engineering confidence

Confidence that the prepared remediation can reduce the actual `/ui/` initial
payload below 510,780 gzip bytes is **high** because it removes synchronous page
modules and the default-route CodeBlock/Prism path, while the analyzer prevents a
filename-only false pass. This assessment is not a measured production result.

## Source responsibility / LOC gate

`frontend/scripts/verify-source-size.mjs` must continue to:

- report path and physical LOC;
- distinguish review threshold from hard target;
- exclude generated/output directories and untouched legacy PHP;
- fail when a hard target is exceeded without a narrow reasoned exception;
- reject stale exceptions;
- preserve human responsibility review below numeric thresholds.

## Bundle/initial-route gate

The final Phase B implementation/evidence must:

- calculate entry + static imports + default-route lazy/static imports with
  shared chunks de-duplicated;
- use gzip measurement comparable to the Vite 7 historical baseline;
- keep 510,780 bytes visible as the prior baseline;
- report entry, AppShell, default-route and actual initial-transfer raw/gzip
  values plus top chunks;
- reject actual initial transfer `>=510780`;
- require all five foundation route source modules to remain reachable dynamic
  entries;
- derive a tighter forward budget only from the real passing measurement.

## Phase B evidence still required

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

If the actual gzip value is below 510,780, set a measured forward budget plus
limited headroom and rerun:

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

The final Phase B record must include exact SHA, Node/npm versions, source-size
output, chunk table, actual initial-route gzip, improvement, forward budget,
coverage, exceptions, command exit codes and review-checklist audit.

## Current runtime blocker retained

The prior command environment returned `EAI_AGAIN registry.npmjs.org`, had an
effectively empty npm cache and no trusted project `node_modules`. Therefore the
real npm/Vite evidence remains unclaimed.

## Scope after owner decision

The Phase A branch may be non-force fast-forwarded to `main`. WP-05 may then
start, limited to Session, CSRF, tenant, capability/RBAC and object-scope security
foundation.

Still prohibited during WP-05 unless separately authorized:

- Calendar, Watchtower, Tickets or another business feature;
- production Apache `/ui/*` fallback;
- Go/go-zero, clustering or SOC runtime;
- GitHub Actions, Pull Request, force push or upstream write.
