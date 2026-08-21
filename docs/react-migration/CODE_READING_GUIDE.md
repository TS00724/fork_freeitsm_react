# WP-03 React/EUI code-reading guide

G1 architecture decisions were supplied by the user on 2026-08-21. This guide now reflects the accepted `/ui/` mount, Apache-first deployment, Light default, PHP/EUI shell mapping, identity hierarchy, locale/timezone policy, hybrid API typing, Calendar pilot recommendation and Playwright quality strategy.

## 1. `frontend/package.json`

- React 18, EUI/Borealis, Vite, React Router, strict TypeScript, Vitest and Testing Library remain the foundation.
- WP-03 adds `@playwright/test` and `@axe-core/playwright` plus local scripts for E2E and accessibility.
- The earlier npm registry failure is historical. A real `package-lock.json`
  now exists and `npm ci` has succeeded. The non-browser `npm run verify` gate
  also passes; see the verification report for exact coverage and build output.
  Playwright/axe and Human QoS retain separate pending states.

## 2. `frontend/src/main.tsx`

- Keeps startup small and synchronous.
- Loads only public runtime config and mounts one StrictMode root.
- No session/bootstrap/business fetch is added in WP-03.

## 3. Providers

Read `frontend/src/app/providers/AppProviders.tsx`, then providers under `config/`, `theme/`, `i18n/`, `auth/`, `tenants/`, and `permissions/`.

Accepted order remains:

```text
runtime config
→ EUI/theme
→ locale/timezone
→ auth
→ tenant
→ permission
→ BrowserRouter
```

Auth, tenant and permission providers remain unresolved placeholders. They reserve seams; they do not authorize anything.

## 4. Router and mount prefix

Read `frontend/src/app/router.tsx`, `App.tsx`, `config/runtimeConfig.ts`, and `index.html`.

G1 accepted `${BASE_URL}ui/` as the React prefix. Examples:

```text
/ui/
/ui/architecture
/ui/forbidden
/ui/error
/ui/<unknown> → React 404
```

The legacy PHP routes remain unchanged. Production Apache fallback for `/ui/*`
is deliberately not implemented or verified because root `.htaccess` also
protects long-lived auth/callback/CSAT/QR contracts and needs a separate reviewed
change. The local `preview:test` server validates `/ui/` asset MIME types and SPA
deep-link fallback for the built artifact, but it does not validate Apache
refresh or rewrite behavior.

## 5. Runtime config defaults

Approved defaults are:

```text
appPath: ui/
locale: en
timezone: UTC
colorMode: light
```

`BASE_URL` remains runtime configurable for root and subdirectory installs. Timezone is independent from locale.

## 6. API client placeholder

Read `frontend/src/api/client.ts`.

It remains a same-origin transport abstraction only. It may carry a future CSRF header but defines no server route or payload schema in WP-03.

Future rule:

```text
/api/v1      = machine/integration Bearer API
/api/ui/v1   = browser same-origin Session UI API
```

Do not put `/api/v1` keys in the browser.

## 7. Future auth/CSRF direction

Read `docs/react-migration/ADR-002-ui-api-auth-csrf.md`.

Accepted direction:

- retain server-side PHP Session during the PHP backend phase;
- React never reads the session ID;
- `401` means missing/expired authentication;
- `403` means authenticated but forbidden;
- state-changing UI requests use a session-bound synchronizer CSRF token;
- layer CSRF with SameSite and Origin/Referer validation;
- server authorization remains authoritative.

Exact JSON fields and endpoint contracts are deferred to later BFF/security work.

## 8. AppShell

Read `frontend/src/layouts/AppShell.tsx`, `includes/header.php`, and `docs/react-migration/PHP_EUI_SHELL_MAPPING.md`.

The goal is not to copy legacy markup. Preserve the familiar information architecture while using EUI components. Unmigrated modules remain legacy links during strangler migration.

## 9. Theme

Read `frontend/src/theme/ThemeProvider.tsx`.

Initial mode is now Light via runtime defaults. Dark remains available through the review toggle. Later account/SOC preference ownership is not implemented yet.

## 10. Locale, timezone and SOC identity

Minimum React locale target:

```text
English
zh-CN
zh-TW
```

Existing FreeITSM language sources should be reused where possible. Timezone remains separately configurable.

Future identity architecture is:

```text
SOC level-1 identity
       ↓ adapter/context
FreeITSM level-2 analyst/company/role/capability context
```

No SOC integration is implemented in WP-03.

## 11. API type policy

Generate wire DTOs, enums and schema-derived contract types from OpenAPI/JSON Schema, then map to handwritten frontend domain/view models. Do not bind component state directly to PHP payload shapes.

## 12. Playwright and accessibility

Read:

- `frontend/playwright.config.ts`
- `frontend/e2e/g1-shell.spec.ts`
- `frontend/e2e/a11y.spec.ts`
- `docs/react-migration/TEST_STRATEGY.md`

Playwright is the primary browser framework; Selenium is fallback only. The initial suite targets Chromium, Firefox and WebKit, `/ui/` direct load, Light default, 403/404 deep links and axe serious/critical violations.

## 13. Recommended first vertical slice

Calendar remains the accepted first-post-security recommendation from D-018,
but it conflicts with the original master-plan placement of Watchtower in WP-08
and Calendar in WP-24. WP-04 and WP-05 are unaffected. All Calendar, Watchtower,
Tickets, and other pilot work is frozen until G2 records and applies one coherent
package schedule.

## 14. G1 result and current stop

Accepted G1 architecture decisions are recorded in
`ADR-001-g1-architecture-decisions.md` and `progress/DECISION_LOG.md`.

The dependency-install blocker and all non-browser automated gates are resolved.
Playwright/axe browser execution, user Human QoS, and owner/legal EUI-term
acceptance remain explicit closure items. WP-04/05 may begin only after the
current verification-closure result permits it, and feature pilots remain frozen
through G2 replanning.

No GitHub Actions workflow or pull request is part of this work. Go/go-zero and
cluster implementation remain future-only.
