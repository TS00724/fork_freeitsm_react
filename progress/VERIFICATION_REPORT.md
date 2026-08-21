# FreeITSM React migration verification report

Report date: 2026-08-21  
Target repository: `TS00724/fork_freeitsm_react`  
WP-03 start SHA: `2e7e15f7f1e84d171aec50e14b207013333802db`

## Existing WP-01 / WP-02 verification state

The repository still records WP-01 effective progress 90% and WP-02 effective progress 35%. Those values are not rewritten upward in WP-03 because the current environment still lacks the target local checkout evidence used by WP-01 and still lacks a trustworthy project lockfile/dependency install for WP-02.

## WP-03 — G1 architecture walkthrough and ADRs

### Source verification performed

| Check | Result | Evidence |
|---|---|---|
| Remote `main` re-read before work | Pass | `main` was `2e7e15f7f1e84d171aec50e14b207013333802db` |
| G1 decision source | Pass | User supplied mount/server/shell/theme/auth/identity/i18n/type/pilot/test decisions on 2026-08-21 |
| Current PHP shell inspected | Pass | `includes/header.php` confirms familiar Inbox/Reports/Users/Assets/Settings/Logs/Knowledge/Calendar/user/logout structure |
| Existing CSRF limitation inspected | Pass | `includes/request_guard.php` documents no complete CSRF-token mechanism |
| Existing session hardening inspected | Pass | `includes/session_security.php` handles session rotation and hardened cookie attributes |
| Public machine API boundary inspected | Pass | `/api/v1` remains Bearer API-key based and separate from browser Session auth |
| React mount decision applied | Source authored | Runtime defaults and index defaults changed from `app/` to `ui/` |
| Light default applied | Source authored | Runtime/index default changed from `system` to `light` |
| Playwright architecture | Source authored | `@playwright/test` 1.62.1, Chromium/Firefox/WebKit config, shell/deep-link tests |
| Accessibility automation | Source authored | `@axe-core/playwright` 4.13.0 with serious/critical violation blocking |
| PHP/EUI shell mapping | Documented | `docs/react-migration/PHP_EUI_SHELL_MAPPING.md` |
| Future auth/CSRF direction | Documented only | ADR-002; no server handler created |
| GitHub Actions | Pass by scope | No workflow file authored or required |
| Business module migration | Pass by scope | None authored |
| BFF implementation | Pass by scope | None authored |

### Dependency-backed commands

The following are **not reported as executed in WP-03** because the pre-existing npm registry/lockfile blocker remains unresolved in this execution environment:

```text
npm ci
npm run typecheck
npm run lint
npm run test
npm run build
npx playwright install
npm run test:e2e
npm run test:a11y
```

### Playwright package selection

On 2026-08-21, current npm registry information was checked before pinning the test scaffolding:

- `@playwright/test`: `1.62.1`
- `@axe-core/playwright`: `4.13.0`

These pins are provisional until a real project lockfile is generated and reviewed.

### WP-03 result

Implementation 95%, API/Contract direction 100%, Verification 45%, Docs/Handoff 100%. Effective progress is **45%**, Confidence **No**, status **In progress**.

The missing evidence is dependency-backed execution of the updated runtime tests and Playwright/axe suite. WP-03 must not be marked `Verified complete` until a trustworthy lockfile/install is available and the applicable commands pass.

## No-scope-creep confirmation

WP-03 does not implement:

- `/api/ui/v1` PHP handlers;
- session/bootstrap endpoints;
- CSRF generation/validation server code;
- tenant/RBAC/object-scope server code;
- Calendar, Watchtower, Tickets, Assets, CMDB or any other business module;
- Nginx or Go/go-zero deployment;
- GitHub Actions.
