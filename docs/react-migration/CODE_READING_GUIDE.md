# WP-02 React/EUI code-reading guide

Read the foundation in this exact order before approving G1. Every section states
what the files do, why the boundary exists, where a future BFF may connect, what
the user may tune, and which decision remains at G1.

## 1. `frontend/package.json` and lockfile status

- **Responsibility:** pins React 18, EUI, Borealis, Vite, React Router, strict TypeScript, ESLint, Vitest, Testing Library, and local scripts.
- **Why:** Node tooling is private to `frontend/`; PHP has no package dependency on it.
- **Future BFF:** none. Tooling must remain transport-neutral.
- **User may adjust:** package manager and reviewed compatible versions.
- **G1 decision:** accept npm and the provisional pins. `package-lock.json` is absent because npm registry DNS failed; read `frontend/DEPENDENCY_INSTALL_BLOCKER.md`. Do not approve full verification before a reviewed lockfile and `npm ci`.

## 2. `frontend/src/main.tsx`

- **Responsibility:** reads public runtime settings, sets locale/timezone document metadata, and mounts one StrictMode root.
- **Why:** startup remains small and has no session or business fetch.
- **Future BFF:** an approved bootstrap provider may be inserted later, not hard-coded in the entry file.
- **User may adjust:** StrictMode and startup diagnostics.
- **G1 decision:** keep a synchronous architecture shell now or introduce an explicit async bootstrap screen after contracts exist.

## 3. Providers

Read `frontend/src/app/providers/AppProviders.tsx`, then providers under `config/`,
`theme/`, `i18n/`, `auth/`, `tenants/`, and `permissions/`.

- **Responsibility:** makes the order runtime config → EUI/theme → locale/timezone → auth → tenant → permission → router visible.
- **Why:** prevents hidden global singletons and keeps every cross-cutting concern replaceable.
- **Future BFF:** reviewed bootstrap/session data replaces the three `unresolved` security contexts.
- **User may adjust:** provider order, preference persistence, and whether bootstrap is one immutable snapshot or split contexts.
- **G1 decision:** confirm separate identity, tenant, and permission boundaries; no global state library is introduced yet.

## 4. Router

Read `frontend/src/app/router.tsx` and `frontend/src/app/App.tsx`.

- **Responsibility:** exposes only foundation, architecture review, 403, generic error, and wildcard 404 routes.
- **Why:** no legacy or business route is claimed before review.
- **Future BFF:** route guards may consume approved state, but they never replace server authorization.
- **User may adjust:** route objects versus JSX, lazy loading, and final path names.
- **G1 decision:** approve `${BASE_URL}app/` as the proposed SPA prefix and define Apache/IIS/nginx fallback rules limited to that prefix.

## 5. Runtime config and `BASE_URL`

Read `frontend/src/config/runtimeConfig.ts`, `RuntimeConfigProvider.tsx`, and the
early script in `frontend/index.html`.

- **Responsibility:** normalizes root/subdirectory deployment, BrowserRouter basename, same-origin UI API placeholder, locale, timezone, and color preference. An early `<base>` supports relative Vite assets on a deep route.
- **Why:** FreeITSM may run under `/freeitsm-app/`; hard-coding `/` would break assets and navigation.
- **Future BFF:** a thin host may inject the same non-secret runtime object.
- **User may adjust:** object name, default SPA prefix, locale, and timezone.
- **G1 decision:** choose static injection, a thin PHP host, or another public runtime-config delivery method.

## 6. API client placeholder

Read `frontend/src/api/client.ts`.

- **Responsibility:** centralizes URL joining, `credentials: 'same-origin'`, AbortSignal pass-through, optional CSRF header injection, content handling, and typed HTTP errors. It defines no endpoint or response envelope.
- **Why:** the browser must not use a long-lived public `/api/v1` key or scatter fetch policy through features.
- **Future BFF:** approved `/api/ui/v1` contracts and generated/validated DTOs may build on this transport after G1/G2.
- **User may adjust:** fetch/query library, retry ownership, and error normalization.
- **G1 decision:** keep transport framework-light and add server-data caching only with the first reviewed contract.

## 7. Auth, tenant, and permission placeholders

Read `frontend/src/auth/AuthBoundary.tsx`, `frontend/src/tenants/TenantBoundary.tsx`,
and `frontend/src/permissions/PermissionBoundary.tsx`.

- **Responsibility:** reserves explicit integration seams without inventing identity, company, role, or capability fields.
- **Why:** hidden UI controls are not authorization; PHP must enforce capability, tenant, and object scope.
- **Future BFF:** reviewed bootstrap/session types replace `unresolved`.
- **User may adjust:** naming and state split.
- **G1 decision:** keep identity and tenant contexts separate.

## 8. AppShell and layouts

Read `frontend/src/layouts/AppShell.tsx` and `frontend/src/styles/global.css`.

- **Responsibility:** provides a minimal EUI header, architecture-only navigation, basename indicator, and theme toggle.
- **Why:** it demonstrates wiring without copying the legacy module menu or deciding the final product shell early.
- **Future BFF:** capability data may later affect navigation, while every request remains server-authorized.
- **User may adjust:** header versus side navigation, density, responsive breakpoints, and branding placement.
- **G1 decision:** select the final shell direction before platform UI work.

## 9. Theme and EUI

Read `frontend/src/theme/ThemeProvider.tsx` and
`docs/react-migration/THIRD_PARTY_REVIEW.md`.

- **Responsibility:** wraps the app once in EUI Borealis and exposes light/dark state.
- **Why:** prevents hard-coded mode/colors and prepares controlled mapping from legacy preferences.
- **Future BFF:** an approved preference/bootstrap field may choose initial mode.
- **User may adjust:** system preference behavior, persistence, and brand tokens.
- **G1 decision:** approve Borealis direction and complete EUI LICENSE/NOTICE review; no legal compatibility conclusion is claimed.

## 10. Feature directory convention

Read `frontend/src/features/README.md`, `components/README.md`, `lib/README.md`, and
`types/README.md`.

- **Responsibility:** future vertical slices own route, page, component, API adapter, type, and test files.
- **Why:** supports strangler migration and prevents an unbounded shared-components folder.
- **Future BFF:** each approved feature owns adapters to machine-readable contracts.
- **User may adjust:** names, depth, and test colocation.
- **G1 decision:** approve feature-first organization. No business feature exists yet.

## 11. Tests and local checks

Read `frontend/tests/*.test.ts*`, `frontend/src/test/setup.ts`, and
`frontend/scripts/verify-*`.

- **Responsibility:** targets BASE_URL normalization, transport policy, direct 403/404 routes, AppShell, theme toggle, structure, isolation, and lockfile presence.
- **Why:** architecture seams should be testable before business logic.
- **Future BFF:** contract and negative authorization tests are added only after server routes exist.
- **User may adjust:** later Playwright timing and test placement.
- **G1 decision:** decide whether browser E2E begins immediately after G1 or with the BFF foundation. Dependency-backed tests are currently blocked and are not reported as passed.

## 12. Build output and PHP isolation

Read `frontend/vite.config.ts`, `frontend/.gitignore`, `frontend/README.md`, and
`frontend/scripts/verify-isolation.mjs`.

- **Responsibility:** builds only to `frontend/dist/`; keeps PHP, BFF, business features, generated output, and secrets outside the frontend source tree.
- **Why:** the legacy PHP application remains independently runnable and unchanged.
- **Future BFF:** a reviewed deployment step may serve or copy immutable assets to a dedicated public prefix.
- **User may adjust:** release packaging and static versus thin-PHP hosting.
- **G1 decision:** choose build-output handoff and web-server fallback while preserving existing callbacks, APIs, downloads, cron, CSAT, and QR routes.

## G1 decision checklist

1. Approve `frontend/` and feature-first organization.
2. Approve npm/version pins only after real lockfile/install review.
3. Confirm `${BASE_URL}app/` and the server fallback exclusions.
4. Choose runtime-config delivery.
5. Confirm provider order and separate security contexts.
6. Confirm Borealis/light-dark direction and EUI license/notice process.
7. Decide when to add server-data caching and browser E2E.
8. Choose build-output publication without writing into PHP sources.
9. Give written approval before WP-03, BFF, session/CSRF/RBAC, or any module begins.
