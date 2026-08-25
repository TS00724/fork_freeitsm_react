# ADJ-001 source-size and bundle results

Date: 2026-08-25  
Repository: `TS00724/fork_freeitsm_react`  
Base `main`: `d7385470fb216cf504aac53667c43e7accf31675`  
Branch: `adj-001-source-size-bundle-governance`  
Status: **Runtime verification pending — not mergeable**

## Acceptance state

| Requirement | State |
|---|---|
| Source-size gate implemented | Complete |
| Responsibility audit | Complete |
| WP-04 multi-responsibility PHP split | Complete |
| Five foundation routes implemented as lazy entries | Complete in source; real Vite manifest pending |
| Actual `/ui/` gzip below 510,780 | Not measured |
| Forward budget from real evidence | Not set |
| Clean `npm ci` | Not run; environment cannot reach npm |
| Full `npm run verify` | Not run |
| PHP regression behavior | 36 tests passed in local materialized harness |
| Review checklist | Static/PHP sections pass; validation blocked |
| Merge to `main` | No |
| WP-05 | Not started |

## Source-size gate

`frontend/scripts/verify-source-size.mjs` uses Node standard-library APIs only.
It scans new React/frontend scripts/tests and `/api/ui/v1` PHP, excludes generated
transport types and generated/build/test-output directories, distinguishes review
and hard thresholds, rejects stale or weak exceptions, and prints physical LOC.

The proposed split tree produced:

```text
17 files
0 review
0 exceptions
0 failures
largest PHP: UiApiRequest.php, 213 LOC
```

A synthetic 301-line `.tsx` file was rejected with exit 1, confirming the React
hard target is enforced.

## Responsibility audit

The previous compact `Http.php` and `Router.php` were below physical LOC limits
but each mixed four distinct responsibilities. The branch replaces them with
compatibility loaders and one focused class per sibling file:

```text
UiApiException.php
UiApiHttpResponse.php
UiApiRequest.php
UiApiRequestContext.php
UiApiRoute.php
UiApiResponseFactory.php
UiApiRouter.php
UiApiKernel.php
```

No exception is required. Public class names, bootstrap entry, route behavior,
response envelopes and OpenAPI contract remain unchanged.

## Bundle gate design

The historical baseline remains:

```text
1,641.07 kB minified
510,780 bytes gzip
```

The analyzer uses Vite's manifest and Node default gzip options. It calculates:

1. entry file;
2. synchronous AppShell/static-import closure;
3. Home/default-route lazy/static-import closure;
4. de-duplicated actual `/ui/` initial JavaScript transfer;
5. improvement bytes and percentage;
6. top ten chunks by gzip size;
7. explicit dynamic-entry status for Home, Architecture, 403, Error and 404.

`bundle-budget.json` intentionally keeps `forwardInitialRouteGzipBytes: null`
until a real production build measures a passing value. `npm run verify` must
fail until that measured budget is committed.

A synthetic manifest test passed and demonstrated shared-chunk de-duplication and
explicit validation of all five lazy routes. This validates the algorithm, not
the real application size.

## Commands actually executed

| Command/check | Exit | Important result |
|---|---:|---|
| `php -v` | 0 | PHP 8.4.23 |
| `node --version` | 0 | v22.16.0 |
| `npm --version` | 0 | 10.9.2 |
| PHP lint over proposed UI API tree | 0 | 15 files valid |
| Existing `api/ui/v1/tests/run.php` against proposed split | 0 | 36 tests, 0 failures |
| `node --check verify-source-size.mjs` | 0 | Valid syntax |
| `node --check verify-bundle-budget.mjs` | 0 | Valid syntax |
| source-size proposed-tree scan | 0 | No review, exception or failure |
| source-size 301-line negative fixture | 1 expected | Oversized TSX rejected |
| synthetic bundle manifest measurement | 0 | Closure/de-dup/lazy-source validation worked |
| npm registry probe | 1 | `EAI_AGAIN registry.npmjs.org` |

## Required real runtime sequence

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

Then commit a forward budget based on the real measured value and limited
headroom, followed by:

```bash
npm run verify:bundle-budget
npm run verify
```

PHP and Git checks:

```bash
find api/ui/v1 -name '*.php' -print0 | xargs -0 -n1 php -l
php api/ui/v1/tests/run.php
git diff --check
```

## Exact blocker

```text
command:
  npm view react@18.3.1 version --fetch-retries=0 --fetch-timeout=5000
exit:
  1
stderr:
  npm error code EAI_AGAIN
  npm error syscall getaddrinfo
  npm error request to https://registry.npmjs.org/react failed
  reason: getaddrinfo EAI_AGAIN registry.npmjs.org
```

`github.com` also does not resolve in the command container. The npm cache is
approximately empty and no trusted project dependency tree exists. Therefore a
clean install, Vite production build, real chunk table and final budget cannot be
claimed in this execution.

## Universal-code-writing audit

### Correctness

Pass for source design, accounting algorithm and PHP behavior. Real bundle
improvement remains unproven until production build evidence exists.

### Compatibility

PHP's public class names, `bootstrap.php`, endpoint behavior, response envelopes
and test suite remain compatible. React basename and observable foundation route
expectations are preserved in source/tests. Full frontend compatibility gate is
pending.

### Security/privacy

No Session, CSRF, tenant, RBAC, object-scope, CORS grant, machine API key,
credential or business-route implementation was added.

### Maintainability

Multi-responsibility PHP files were split by domain role, not line slicing.
No source-size exception is used. Router remains below its orchestration limit.

### Validation

PHP and dependency-free static checks pass. Clean npm install, TypeScript, lint,
Vitest coverage, real Vite build, bundle measurement and full verify are blocked
by DNS/network and remain unclaimed.

## Completion decision

ADJ-001 is **not Verified complete**. Keep this branch for runtime verification,
do not fast-forward `main`, and do not start WP-05.
