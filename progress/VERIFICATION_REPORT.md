# FreeITSM React migration verification report

- Report date: 2026-08-21
- Repository: `TS00724/fork_freeitsm_react`
- Verification branch: `agent/g1-verification-closure`
- Start SHA: `d78be7534b36b7b596a1e5ddc1bbedd4b3640b16`
- Verified source SHA: `46c901597557abe7f319a880c9a3539105307196`

## Outcome

G1 architecture is approved. The dependency blocker and all non-browser
engineering gates are closed. WP-01 is now `Verified complete`; WP-02 and WP-03
remain closure-pending because the browser, human, and license decisions below
are required by the accepted quality policy.

No PHP UI-BFF, server auth/CSRF/RBAC implementation, Apache rule, business
feature, GitHub Actions workflow, or pull request was created.

## Repository and scope evidence

| Check | Result |
|---|---|
| Literal target checkout/status | Pass; clean after source commit `46c9015` |
| Configured Git remote | Only `origin=https://github.com/TS00724/fork_freeitsm_react.git` for fetch/push |
| Upstream remote / force push / PR | None |
| `.github/workflows` | Absent |
| Frontend isolation | Pass; no PHP, BFF, or business feature below `frontend/` |
| Go/go-zero / clustering | Not implemented; future-only |

## Dependency and license evidence

| Check | Result |
|---|---|
| Lockfile | Real npm lockfile v3 generated and committed |
| Clean install | `npm ci` exit 0; 464 packages installed |
| React/EUI peer graph | React 18.3.1, EUI 119.0.0, Borealis 8.0.0 and required peers resolve compatibly |
| PrismJS advisory | Explicit override resolves PrismJS 1.30.0; no incompatible EUI downgrade accepted |
| Production audit | Exit 0; 0 vulnerabilities reported |
| Complete lockfile audit | Exit 0; 0 vulnerabilities reported across 514 dependency entries |
| EUI/theme license | **Pending owner/legal acceptance**; default SSPL v1 / Elastic License 2.0 is not MIT/OSI-default |

Audit results are the 2026-08-21 registry snapshot, not a permanent guarantee.
The release checklist is `frontend/THIRD_PARTY_NOTICES.md`.

## Final non-browser quality gate

Final `npm run verify`: **exit 0**.

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

The Vite build retained a visible performance warning: the main minified chunk
is 1,641.07 kB (510.78 kB gzip). Treat code splitting and perceived load time as
a recorded risk for Human QoS, not as a hidden or waived warning.

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

## Browser and accessibility status

`npx playwright install` failed with truncated/non-ZIP download bodies and an
HTTP 502 proxy certificate-time error. The official alternate download host
also returned a gateway HTTP 400. No system browser was present.

`npm run test:e2e` then started the production build and static server but exited
1 because every project stopped at `browserType.launch`: Chromium, Firefox, and
WebKit executables were absent. Therefore:

- 18 configured browser cases discovered;
- 0 cases reached page assertions;
- Playwright shell/deep-link assertions: **Blocked, not passed**;
- axe serious/critical checks: **Blocked, not passed**.

This is an external execution blocker, but it remains real missing evidence.
Run the commands on a machine that can install Playwright's pinned browser
binaries before calling WP-02/WP-03 verified complete.

## Corrected failures

The record retains failures encountered and fixed during closure:

1. ESLint typed rules incorrectly applied to `.mjs` scripts — corrected with a
   JavaScript-only non-type-checked override.
2. Two typed lint findings in placeholder pages/boundaries — corrected.
3. Vite preview returned HTML for `/ui/assets/*` — replaced in E2E with a narrow
   local static mount that verifies real asset MIME and deep-link fallback.

The final green command was run only after those corrections.

## Closure decision required

Before starting WP-04/WP-05, record all of the following:

1. Playwright/axe result from an environment with installed browsers.
2. User Human QoS result: `Pass`, `Needs tuning`, or `Block`.
3. Owner/legal EUI/Elastic term result: `Accepted`, `Rejected`, or
   `Needs legal review`.

Production Apache `/ui/*` fallback is not claimed here. It remains a separate,
narrowly reviewed server integration task and must preserve legacy auth,
callback, API, CSAT, QR, file, cron, webhook, and stream routes.

Full command-level evidence is in
`progress/verification/WP-03-g1-closure.md`.
