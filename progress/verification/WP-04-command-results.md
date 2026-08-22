# WP-04 command-level verification evidence

- Date: 2026-08-22
- Repository: `TS00724/fork_freeitsm_react`
- Start SHA: `0bf3eb1516eae4be56689e22fa263c8d89e44821`
- Release branch: `wp-04-ui-api-contract-final-v3`
- Scope: `/api/ui/v1` transport and machine-readable contracts only

The final release-branch archive was materialized into a clean local directory.
The commands below were executed against that materialized tree, not against a
partial connector response.

## Environment

```text
Node: v22.16.0
npm: 10.9.2
PHP CLI: 8.4.23
Git: 2.47.3
```

The project lockfile remains unchanged; WP-04 adds no package dependency.

## Results

| Command | Exit | Important result |
|---|---:|---|
| `find api/ui/v1 -name '*.php' -print0 | xargs -0 -n1 php -l` | 0 | All 7 PHP files passed syntax validation |
| `php api/ui/v1/tests/run.php` | 0 | 36 tests, 0 failures |
| `php -r '<decode api/ui/v1/openapi.json and assert OpenAPI 3.1/statuses>'` | 0 | Contract is valid JSON and contains all required status semantics |
| `node frontend/scripts/generate-ui-contract.mjs --check` | 0 | Generated TypeScript exactly matches OpenAPI components schemas |
| `node --check frontend/scripts/generate-ui-contract.mjs` | 0 | Generator is valid JavaScript syntax |
| `cd frontend && npm ci --ignore-scripts` | 0 | Clean reproducible lockfile install |
| `cd frontend && npm run verify` | 0 | Structure/isolation/lockfile/contract drift/typecheck/lint/43 tests/coverage/build/preview all passed |
| executable PHP forbidden-scope scan | 0 | No Session start, DB config include, machine Authorization/Bearer handling or business feature route |
| `.github/workflows` absence check | 0 | No workflow directory exists |

## PHP test inventory

The test runner covers foundation and process-health envelopes; routing with and
without `PATH_INFO`; HEAD/OPTIONS/404/405; malformed and non-object JSON; media
types including `application/*+json`; request/correlation IDs; raw/encoded path
attacks; typed parameters; generic non-leaking 500s; 401/403/409/422/429
semantics; no CORS; no executable Session, DB-config or machine-API binding; and
OpenAPI schema/status presence.

## Frontend verification detail

`npm run verify` now checks `npm run verify:ui-contract` before TypeScript/lint/
tests/build. The existing frontend suite remains 43 tests, and the previously
accepted coverage thresholds remain green.

The production build continues to report the known foundation warning:

```text
1,641.07 kB minified / 510.78 kB gzip main chunk
```

That warning is not waived. D-029 requires future business routes and heavyweight
components to use route/feature/component-level lazy loading.

## Corrections before final evidence

Two pre-release findings were corrected and retested:

- the first vendor JSON regex could consume the `+` delimiter and reject
  `application/vnd.*+json`;
- an early source-scope scan inspected comments and falsely treated a comment
  mentioning `config.php` as a real include.

The final runner directly tests vendor JSON and strips PHP comment tokens before
checking executable forbidden patterns.

## Explicitly not verified or implemented

- Playwright Chromium/Firefox/WebKit and axe remain owner-approved deferred debt,
  not Passed evidence;
- production Apache `/ui/*` SPA fallback is separate and unverified;
- PHP Session/bootstrap, CSRF, tenant, RBAC/capability and object-scope controls
  are not implemented in WP-04;
- no database, business module, Go/go-zero or SOC runtime was started;
- no GitHub Actions or Pull Request was created or used.
