# ADJ-001 source-size and bundle results

Date: 2026-08-25  
Repository: `TS00724/fork_freeitsm_react`  
Base `main`: `d7385470fb216cf504aac53667c43e7accf31675`  
Branch: `adj-001-source-size-bundle-governance`  
Status: **Phase A owner-accepted; Phase B runtime verification deferred**

## Owner decision

The project owner authorizes Phase A integration and the start of WP-05. Phase B
remains explicit verification debt and is not recorded as Passed.

Required timing:

```text
Complete Phase B before G2 closure or any React production release,
whichever occurs first.
```

If the real `/ui/` initial-route gzip result is `>= 510780`, ADJ-001 becomes a
blocking adjustment again and further measured splitting is required.

## Acceptance state

| Requirement | State |
|---|---|
| Source-size gate implemented | Complete |
| Responsibility audit | Complete |
| WP-04 multi-responsibility PHP split | Complete |
| Five foundation routes implemented as lazy entries | Complete in source; real Vite manifest pending |
| Technical confidence of `<510780` remediation | **High; engineering assessment only** |
| Actual `/ui/` gzip below 510,780 | Not measured |
| Forward budget from real evidence | Not set |
| Clean `npm ci` | Not run; environment cannot reach npm |
| Full `npm run verify` | Not run |
| PHP regression behavior | 36 tests passed in local materialized harness |
| Review checklist | Phase A/PHP sections pass; runtime validation deferred |
| Integration to `main` | Authorized by owner; non-force fast-forward only |
| WP-05 | Authorized after integration |

## Why confidence is high but not proof

The last verified baseline is:

```text
1,641.07 kB minified
510,780 bytes gzip
```

The prepared change removes known synchronous startup dependencies rather than
only renaming chunks:

1. Home, Architecture, 403, Error and 404 page modules move from static imports
   to explicit route-level dynamic imports.
2. Home removes `EuiCodeBlock`, eliminating the direct Prism/Refractor
   code-highlighting path from the default route source.
3. AppShell remains the synchronous route boundary.
4. The analyzer sums and de-duplicates the entry/static closure and default-route
   lazy/static closure, preventing a `main.js`/`vendor.js` filename-only pass.
5. The final gate rejects `>=510780` and requires a measured forward budget.

These properties provide high confidence that the approach can solve the debt,
but they do not provide the missing production-build number.

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

The previous compact `Http.php` and `Router.php` mixed four responsibilities each.
The branch keeps compatibility loaders and moves implementation to focused files:

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

The analyzer uses Vite's manifest and Node default gzip options. It calculates:

1. entry file;
2. synchronous AppShell/static-import closure;
3. Home/default-route lazy/static-import closure;
4. de-duplicated actual `/ui/` initial JavaScript transfer;
5. improvement bytes and percentage;
6. top ten chunks by gzip size;
7. explicit dynamic-entry status for Home, Architecture, 403, Error and 404.

`bundle-budget.json` intentionally keeps `forwardInitialRouteGzipBytes: null`
until a real production build measures a passing value. This unresolved value is
part of the retained Phase B debt.

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

## Deferred Phase B runtime sequence

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

Then set a forward budget based on the real measured value plus limited headroom
and execute:

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

## Exact blocker retained as evidence

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

`github.com` also did not resolve in the command container. The npm cache was
approximately empty and no trusted project dependency tree existed.

## Universal-code-writing audit

### Correctness

Pass for Phase A source design, accounting algorithm and PHP behavior. Real
bundle improvement remains unmeasured.

### Compatibility

PHP public class names, bootstrap, endpoint behavior, response envelopes and test
suite remain compatible. React basename and foundation route expectations are
preserved in source/tests. Full frontend runtime compatibility remains deferred.

### Security/privacy

No Session, CSRF, tenant, RBAC, object-scope, CORS grant, machine API key,
credential or business-route implementation was added.

### Maintainability

Multi-responsibility PHP files were split by domain role, not line slicing. No
source-size exception is used. Router remains below its orchestration limit.

### Validation

PHP and dependency-free checks pass. Clean npm install, TypeScript, lint, Vitest
coverage, real Vite build, bundle measurement and full verify are owner-deferred
and remain unclaimed.

## Completion decision

ADJ-001 is **not `Verified complete`**. It is **Phase A owner-accepted with Phase
B deferred**. Integration is authorized so WP-05 can begin, while Phase B remains
mandatory before G2 closure or React production release.
