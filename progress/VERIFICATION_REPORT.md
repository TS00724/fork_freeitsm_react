# FreeITSM React migration verification report

- Report date: 2026-08-25
- Repository: `TS00724/fork_freeitsm_react`
- WP-04 start SHA: `0bf3eb1516eae4be56689e22fa263c8d89e44821`
- Current scope: WP-04 complete; ADJ-001 Phase A accepted; WP-05 authorized

## WP-04 outcome

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
| WP-04 required base | `main` was `0bf3eb1516eae4be56689e22fa263c8d89e44821` before WP-04 |
| Repository | Only `TS00724/fork_freeitsm_react` |
| Remote/upstream | User fork only; no upstream remote or write |
| Pull Request / force push | None |
| `.github/workflows` | Absent and not used |
| Root `.htaccess` | Unchanged |
| Existing `/api/v1` | Unchanged Bearer machine surface |
| PHP UI / business modules | Unchanged |

## WP-04 delivered routes

```text
GET|HEAD|OPTIONS /api/ui/v1/
GET|HEAD|OPTIONS /api/ui/v1/health
```

`/api/ui/v1/openapi.json` is a static OpenAPI 3.1 source. Any other executable
path is dispatched through the single `api/ui/v1/index.php` front controller and
returns the common JSON 404 or 405 envelope.

## WP-04 command evidence

The final WP-04 release-branch archive was materialized into a clean local
verification directory before the following commands were executed. Complete
output is summarized in `progress/verification/WP-04-command-results.md`.

| Command | Exit | Result |
|---|---:|---|
| `find api/ui/v1 -name '*.php' -print0 \| xargs -0 -n1 php -l` | 0 | All WP-04 PHP files had valid syntax |
| `php api/ui/v1/tests/run.php` | 0 | 36 contract, routing and security-negative tests; 0 failures |
| OpenAPI JSON decode/version assertion | 0 | Valid JSON; OpenAPI `3.1.0`; required status semantics present |
| `node frontend/scripts/generate-ui-contract.mjs --check` | 0 | Committed TypeScript exactly matches OpenAPI schemas |
| `node --check frontend/scripts/generate-ui-contract.mjs` | 0 | Generator syntax valid |
| `cd frontend && npm ci --ignore-scripts` | 0 | Reproducible lockfile install; no package manifest drift |
| `cd frontend && npm run verify` | 0 | Structure, isolation, lockfile, contract drift, typecheck, lint, 43 tests/coverage, build and `/ui/` preview probe passed |
| UI API forbidden-scope scan | 0 | No Session start, machine Authorization/Bearer handling, DB config include or business route |
| `.github/workflows` absence check | 0 | No workflow directory |

## WP-04 PHP contract coverage

The 36-test runner verifies foundation and process-health success envelopes;
`HEAD` and route-specific `OPTIONS`/`Allow`; routing with and without
`PATH_INFO`; 404/405; malformed/non-object JSON and media types; request and
correlation IDs; unsafe path encodings; typed route parameters; generic
non-leaking 500s; 401/403/409/422/429 semantics; no CORS; no Session/machine
API/DB bootstrap; and OpenAPI schema/status presence.

## Frontend contract verification

The dependency-free generator emits transport DTOs/enums under:

```text
frontend/src/api/generated/ui-contract.ts
```

`npm run verify:ui-contract` is part of the normal frontend verification gate.
Generated types are not imported as React domain/view state, and WP-04 adds no
browser business request.

## ADJ-001 Phase A accepted evidence

The owner has accepted ADJ-001 Phase A into the baseline. The accepted scope
includes:

- dependency-free source-size audit and explicit exception policy;
- responsibility-based split of the WP-04 HTTP/router classes into focused
  modules while retaining compatibility loaders;
- lazy imports for Home, Architecture, 403, generic error and 404 routes;
- removal of `EuiCodeBlock`/Prism from the default Home route;
- Vite manifest-based calculation of entry, synchronous shell, default route and
  de-duplicated actual `/ui/` initial JavaScript dependency closures;
- preservation of the historical `510,780`-byte gzip baseline as a fail-closed
  comparison;
- source-size negative test and PHP syntax/36-contract-test behavior evidence;
- prepared handoff and runtime command sequence.

Engineering confidence that these changes can produce a real `/ui/` initial
route below `510,780` gzip bytes is **Yes**. This confidence is based on the
structural removal of non-startup route modules and CodeBlock/Prism from the
initial dependency graph, plus a verifier that rejects manual-chunk-only
relabeling.

## ADJ-001 Phase B deferred — not Passed

The following commands/results remain unavailable in the current runtime and are
not claimed as successful:

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

Consequently the following facts remain unknown:

- real post-split `/ui/` initial-route raw/gzip bytes;
- measured improvement bytes and percentage;
- real top-10 JavaScript chunks;
- evidence-based `forwardInitialRouteGzipBytes`;
- final `npm run verify:bundle-budget` and integrated frontend verification.

The owner explicitly permits WP-05 to start while this evidence is deferred.
This is a gate change, not a test waiver or a fabricated Pass. ADJ-001 remains at
60% effective progress with Confidence No for verified completion.

To keep WP-05 development usable, the default `npm run verify` continues to run
the source-size gate but temporarily excludes the unresolved forward bundle
budget. The explicit Phase B command is:

```bash
cd frontend
npm ci
npm run verify:adj001-phase-b
```

After recording the real result, set `frontend/bundle-budget.json`, run:

```bash
npm run verify:bundle-budget
npm run verify
```

ADJ-001 Phase B is mandatory before G2 closes and before any production or
performance acceptance statement.

## Review-audit status for the gate change

### Correctness

- No unmeasured gzip value is presented as fact.
- The `510,780`-byte baseline remains visible and enforceable.
- WP-05 authorization does not alter the ADJ analyzer or lazy-route behavior.

### Compatibility

- Existing `/ui/` basename, WP-04 API/OpenAPI contracts and generated transport
  boundary remain unchanged.
- The package adds an explicit deferred Phase B command while preserving normal
  source-size/type/lint/test/build/preview verification.

### Security/privacy

- No Session, CSRF, tenant, RBAC/object-scope implementation is introduced by
  this transition record.
- No secrets, machine API keys or cross-origin credential behavior is added.

### Maintainability

- Source-size governance remains in the default verify path.
- Responsibility-based modules remain the required pattern; mechanical slicing
  is still prohibited.

### Validation

- Phase B is explicitly `Deferred / Not verified`.
- G2 cannot close until the missing runtime evidence is supplied.

## Deferred and unclaimed evidence

By explicit G1 owner decision, Playwright Chromium/Firefox/WebKit and axe remain
deferred to a later Codex/script-capable environment. They are **not** recorded
as Passed.

Production Apache `/ui/*` SPA fallback remains unimplemented and unverified.
Only the narrow `/api/ui/v1/.htaccess` front-controller rule is currently in
scope.

## Current transition decision

WP-04 remains complete. ADJ-001 Phase A is accepted, while Phase B is retained as
pre-G2 verification debt. WP-05 is authorized to start, but Calendar,
Watchtower, Tickets and all other business features remain frozen until the
security foundation and G2 review permit them.
