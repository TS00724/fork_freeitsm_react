# FreeITSM React migration verification report

- Report date: 2026-08-22
- Repository: `TS00724/fork_freeitsm_react`
- Original verification branch: `agent/g1-verification-closure`
- G1 closure baseline: `132d77e2e7ea88910bee3fa45819ac7e18c635ec`
- Verified implementation source: `46c901597557abe7f319a880c9a3539105307196`
- Final G1 status: **Closed by explicit owner decision**

## Outcome

G1 architecture and G1 Human QoS are approved. The dependency blocker and all
non-browser engineering gates are closed. WP-01, WP-02 and WP-03 are now
`Verified complete` for their G1 scope under the owner-approved closure policy.

The owner explicitly revised one gate on 2026-08-22: the pinned Playwright
Chromium/Firefox/WebKit + axe rerun is deferred to a later Codex/script-enabled
environment and is no longer a G1 closure blocker. It remains **not passed** and
must not be reported as successful until real browser execution completes.

No PHP UI-BFF, server auth/CSRF/RBAC implementation, Apache rule, business
feature, GitHub Actions workflow, pull request, Go/go-zero runtime or SOC cluster
implementation was created in this closure work.

## Repository and scope evidence

| Check | Result |
|---|---|
| G1 closure baseline | `132d77e2e7ea88910bee3fa45819ac7e18c635ec` |
| Literal target checkout/status from earlier closure | Pass; clean after implementation source commit `46c9015` |
| Configured Git remote from earlier closure | Only `origin=https://github.com/TS00724/fork_freeitsm_react.git` for fetch/push |
| Upstream remote / force push / PR | None |
| `.github/workflows` | Absent / not used |
| Frontend isolation | Pass; no PHP, BFF, or business feature below `frontend/` |
| Go/go-zero / clustering | Not implemented; future-only |

A later G1 runtime attempt on 2026-08-22 could not materialize a fresh clone
because the execution environment could not resolve GitHub/npm/Playwright hosts.
That blocked attempt is preserved separately and is not presented as repository
failure or browser success.

## Dependency and license evidence

| Check | Result |
|---|---|
| Lockfile | Real npm lockfile v3 generated and committed |
| Clean install | Earlier verified `npm ci` exit 0; 464 packages installed |
| React/EUI peer graph | React 18.3.1, EUI 119.0.0, Borealis 8.0.0 and required peers resolve compatibly |
| PrismJS advisory | Explicit override resolves PrismJS 1.30.0; no incompatible EUI downgrade accepted |
| Production audit | Exit 0; 0 vulnerabilities reported at the 2026-08-21 snapshot |
| Complete lockfile audit | Exit 0; 0 vulnerabilities reported across 514 dependency entries at the snapshot |
| EUI/theme license | **Accepted by project owner on 2026-08-22** |

Owner acceptance requires applicable third-party LICENSE/NOTICE material to be
preserved. If the deployment model later becomes distributed or a managed
service, the EUI/Elastic terms must be reviewed again. This is a project decision,
not legal advice and not a relicensing of Elastic dependencies under MIT.

## Final non-browser quality gate

The final recorded `npm run verify` against the reviewed implementation source
exited **0**.

| Gate | Result |
|---|---|
| Structure | Pass; 16 required files |
| PHP/React isolation | Pass |
| Lockfile check | Pass |
| Strict TypeScript | Pass, including E2E project reference |
| ESLint | Pass with zero warnings/errors |
| Unit/component/security tests | 3 files / 43 tests passed |
| Coverage | Statements 84.01%, branches 78.53%, functions 84.84%, lines 87.83%; all thresholds passed |
| Production build | Pass; 2,730 modules transformed; sourcemaps disabled |
| `/ui/` production-artifact probe | Pass; shell/deep-link HTML, JavaScript asset MIME, missing asset 404, and outside-mount 404 verified |

## Bundle/performance result and mandatory architecture constraint

The Vite build retained a visible performance warning: the main minified chunk
is **1,641.07 kB / 510.78 kB gzip**. This risk is accepted for G1 only and is not
a future bundle-size baseline.

The owner requires the following from post-G1 frontend implementation:

- business routes default to `React.lazy()` / dynamic `import()` or an equivalent
  route-level lazy mechanism;
- AppShell synchronously loads only startup-critical platform code;
- heavy feature-only dependencies (for example DataGrid, charts, editors,
  CodeBlock/Prism, CMDB/Mapper/diagramming) are split at feature/component level
  where practical;
- Calendar, Watchtower, Tickets, CMDB and other business modules must not be
  allowed to grow the initial shell chunk by default;
- a later Codex/script work period should add bundle analyzer/treemap evidence so
  EUI/React/Prism/application contributions are measured instead of guessed.

The 510.78 kB gzip warning remains visible technical debt.

## Security corrections verified by tests

- runtime base, app path, and API base use WHATWG URL parsing with same-origin
  and deployment-base containment;
- backslashes, control characters, encoded separators, protocol-relative URLs,
  schemes, and single/double-encoded dot-segment escapes are rejected;
- locale is limited to canonical `en`, `zh-CN`, or `zh-TW`; IANA timezone and
  `light|dark|system` color mode are runtime-validated;
- API endpoints cannot escape the configured `/api/ui/v1/` base;
- unsafe methods fail closed before fetch when no non-empty CSRF token exists;
- `credentials: same-origin` cannot be overridden and the custom `expect` option
  is not leaked into `fetch`.

These are frontend transport protections only. Server-side session, CSRF,
tenant, capability, and object-scope enforcement still belongs to WP-04/WP-05.

## Browser and accessibility status — deferred, not passed

Previous browser installation attempts failed at external download/proxy/DNS
boundaries before pinned browser assertions could complete. The 2026-08-22
runtime attempt is recorded in:

`progress/verification/WP-03-g1-runtime-attempt-2026-08-22.md`

Current truth:

- Chromium: **deferred; not passed**;
- Firefox: **deferred; not passed**;
- WebKit: **deferred; not passed**;
- axe serious/critical: **deferred; not passed**.

By explicit owner decision on 2026-08-22, this matrix will be executed later via
Codex/scripts and no longer blocks G1 closure. A future failure must be fixed and
recorded; the deferment must never be rewritten as historical success.

## Human QoS decision

Project owner decision on 2026-08-22:

| Area | Result |
|---|---|
| AppShell + `/ui/` architecture | Pass |
| Theme and interaction foundation | Pass |
| Performance/bundle direction | Pass with mandatory lazy/code-splitting requirement |
| Provider/code maintainability | Pass |
| Overall Human QoS | **Pass** |

## Corrected failures retained for history

1. ESLint typed rules incorrectly applied to `.mjs` scripts — corrected with a
   JavaScript-only non-type-checked override.
2. Two typed lint findings in placeholder pages/boundaries — corrected.
3. Vite preview returned HTML for `/ui/assets/*` — replaced in E2E with a narrow
   local static mount that verifies real asset MIME and deep-link fallback.
4. Browser binary installation/launch evidence remains deferred and is not
   converted into a pass.

## G1 closure and next boundary

G1 is closed. WP-02 and WP-03 are `Verified complete` under the revised closure
policy recorded in `progress/DECISION_LOG.md` and
`progress/G1_CLOSURE_CHECKLIST.md`.

Production Apache `/ui/*` fallback remains separately unimplemented and must
preserve legacy auth, callback, API, CSAT, QR, file, cron, webhook and stream
routes when it is eventually reviewed.

WP-04 has **not started in this closure work period**. A subsequent prompt may
begin WP-04 from the final merged G1 closure commit. The browser matrix remains
open verification debt for later Codex/script execution.
