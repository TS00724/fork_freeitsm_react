# FreeITSM React migration verification report

- Report date: 2026-08-22
- Repository: `TS00724/fork_freeitsm_react`
- WP-04 start SHA: `0bf3eb1516eae4be56689e22fa263c8d89e44821`
- Verification branch: `wp-04-ui-api-contract-final-v3`
- Scope: WP-04 `/api/ui/v1` front controller and contracts only

## Outcome

WP-04 is `Verified complete`: implementation, API/contract, verification and
documentation are 100%, Confidence Yes. This result covers the transport and
contract foundation only. It does **not** claim that PHP Session, CSRF, tenant,
RBAC, capability or object-scope enforcement is implemented; that work remains
WP-05.

No business feature, database access, root `.htaccess` edit, GitHub Actions
workflow, Pull Request, upstream write, Go/go-zero or SOC runtime was created.

## Repository boundary

| Check | Result |
|---|---|
| Required base | `main` was `0bf3eb1516eae4be56689e22fa263c8d89e44821` before work |
| Repository | Only `TS00724/fork_freeitsm_react` |
| Remote/upstream | User fork only; no upstream remote or write |
| Pull Request / force push | None |
| `.github/workflows` | Absent and not used |
| Root `.htaccess` | Unchanged |
| Existing `/api/v1` | Unchanged Bearer machine surface |
| PHP UI / business modules | Unchanged |

## Delivered routes

```text
GET|HEAD|OPTIONS /api/ui/v1/
GET|HEAD|OPTIONS /api/ui/v1/health
```

`/api/ui/v1/openapi.json` is a static OpenAPI 3.1 source. Any other executable
path is dispatched through the single `api/ui/v1/index.php` front controller and
returns the common JSON 404 or 405 envelope.

## Command evidence

The final branch archive was materialized into a clean local verification
directory before the following commands were executed. Complete output is
summarized in `progress/verification/WP-04-command-results.md`.

| Command | Exit | Result |
|---|---:|---|
| `find api/ui/v1 -name '*.php' -print0 \| xargs -0 -n1 php -l` | 0 | All 7 WP-04 PHP files have valid syntax |
| `php api/ui/v1/tests/run.php` | 0 | 36 contract, routing and security-negative tests; 0 failures |
| OpenAPI JSON decode/version assertion | 0 | Valid JSON; OpenAPI `3.1.0`; required status semantics present |
| `node frontend/scripts/generate-ui-contract.mjs --check` | 0 | Committed TypeScript exactly matches OpenAPI schemas |
| `node --check frontend/scripts/generate-ui-contract.mjs` | 0 | Generator syntax valid |
| `cd frontend && npm ci --ignore-scripts` | 0 | Reproducible lockfile install; no package manifest drift |
| `cd frontend && npm run verify` | 0 | Structure, isolation, lockfile, contract drift, typecheck, lint, 43 tests/coverage, build and `/ui/` preview probe passed |
| UI API forbidden-scope scan | 0 | No Session start, machine Authorization/Bearer handling, DB config include or business route |
| `.github/workflows` absence check | 0 | No workflow directory |

## PHP contract coverage

The 36-test runner verifies:

- foundation and process-health success envelopes;
- `HEAD` and route-specific `OPTIONS`/`Allow` behavior;
- unknown-route 404 and known-route 405 behavior;
- operation with and without `PATH_INFO`;
- malformed JSON, top-level arrays/scalars and unsupported media type;
- `application/json; charset=utf-8` and `application/*+json` support;
- validated request/correlation propagation and invalid-ID replacement;
- raw, encoded and double-encoded separators, dot segments, controls and malformed percent encoding;
- typed route-parameter rejection/coercion;
- generic 500 without exception message, stack, path or secret leakage;
- explicit 401, 403, 409, 422 and 429/`Retry-After` semantics;
- no CORS grant, no Session start, no machine API auth and no DB config load;
- OpenAPI schemas and all required status semantics.

## Frontend contract verification

The OpenAPI generator has no third-party runtime dependency. It emits only
transport DTOs/enums under:

```text
frontend/src/api/generated/ui-contract.ts
```

`npm run verify:ui-contract` is now part of the normal frontend verification
gate. Generated types are not imported as React domain/view state, and WP-04
adds no browser business request.

The existing frontend gate remains green with 43 unit/component/security tests
and its accepted coverage thresholds. The production build still emits the
known warning:

```text
main chunk: 1,641.07 kB minified / 510.78 kB gzip
```

This warning is not hidden or waived. G1 requires future business routes and
heavy EUI/editor/chart/mapper components to use route/feature/component-level
lazy loading.

## Corrections retained as evidence

Two issues were found during pre-merge verification and corrected before the
final branch was declared green:

1. the first JSON media-type regex could consume the `+` separator and reject
   `application/vnd.*+json`; the final regex and a dedicated test cover it;
2. an early static scope test scanned comments and mistook a documentation
   mention of `config.php` for a real include; the final test removes PHP comment
   tokens before examining executable source and also tests the real forbidden
   call patterns.

Neither intermediate branch is eligible for merge. Only
`wp-04-ui-api-contract-final-v3` is the release candidate.

## Deferred and unclaimed evidence

By explicit G1 owner decision, Playwright Chromium/Firefox/WebKit and axe remain
deferred to a later Codex/script-capable environment. They are **not** recorded
as Passed. This debt does not invalidate WP-04's PHP/contract result.

Production Apache `/ui/*` SPA fallback remains unimplemented and unverified.
Only the narrow `/api/ui/v1/.htaccess` front-controller rule is part of WP-04.

## Stop decision

WP-04 is complete. WP-05, BFF Session bootstrap, CSRF issuance/validation,
tenant/RBAC/object-scope enforcement and all business features remain not
started. User review is mandatory before the next work period.
