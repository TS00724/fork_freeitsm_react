# FreeITSM React migration decision log

| ID | Date | Decision | Status | Rationale / consequence |
|---|---|---|---|---|
| D-001 | 2026-08-20 | Put all new frontend source in `frontend/`; build to `frontend/dist/` | Accepted for WP-02; G1 review | Prevents PHP/TSX mixing and keeps legacy PHP independently runnable |
| D-002 | 2026-08-20 | Pin React 18.3.1, EUI 119.0.0, Borealis 8.0.0, strict TypeScript and Vite | Provisional; G1/package install review | EUI manifest supports React 17/18; no React 19 adoption |
| D-003 | 2026-08-20 | Use `BrowserRouter` with runtime basename and `${BASE_URL}app/` as proposed prefix | Provisional G1 decision | Keeps SPA fallback narrow and preserves callbacks/APIs; server fallback is not yet implemented |
| D-004 | 2026-08-20 | Provider order is runtime config → EUI/theme → locale/timezone → auth → tenant → permission → router | Provisional G1 decision | Makes cross-cutting boundaries explicit and replaceable |
| D-005 | 2026-08-20 | Auth, tenant, permission, CSRF, and BFF data remain unresolved placeholders | Accepted scope rule | Avoids inventing contracts before WP-03/WP-05 and preserves server authorization responsibility |
| D-006 | 2026-08-20 | Use a framework-light `fetch` transport placeholder with same-origin credentials and optional CSRF hook | Provisional G1 decision | Centralizes browser transport without defining endpoints/envelopes or using public API keys |
| D-007 | 2026-08-20 | Do not fabricate/copy a package lock when npm registry is unavailable | Accepted integrity rule | WP-02 remains Blocked until a real lockfile, `npm ci`, typecheck, lint, test, build, and runtime smoke pass |
| D-008 | 2026-08-20 | EUI licensing/notice acceptance is an owner decision | Open G1 decision | EUI is not declared MIT; no legal compatibility conclusion is made |
| D-009 | 2026-08-20 | Stop after WP-02 at G1 | Mandatory | No WP-03, UI BFF, session/CSRF/RBAC, Watchtower, Tickets, or other modules before user approval |

Decisions may be revised only with an explicit follow-up entry; do not silently
rewrite accepted history.
