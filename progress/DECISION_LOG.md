# FreeITSM React migration decision log

| ID | Date | Decision | Status | Rationale / consequence |
|---|---|---|---|---|
| D-001 | 2026-08-20 | Put all new frontend source in `frontend/`; build to `frontend/dist/` | Accepted | Prevents PHP/TSX mixing and keeps legacy PHP independently runnable |
| D-002 | 2026-08-20 | Pin React 18.3.1, EUI 119.0.0, Borealis 8.0.0, strict TypeScript and Vite | Provisional; package install review remains | EUI manifest supports React 17/18; no React 19 adoption |
| D-003 | 2026-08-20 | Use `BrowserRouter` with runtime basename and `${BASE_URL}app/` as proposed prefix | **Superseded by D-010** | Original G1 proposal retained for history |
| D-004 | 2026-08-20 | Provider order is runtime config → EUI/theme → locale/timezone → auth → tenant → permission → router | Accepted at G1 | Makes cross-cutting boundaries explicit and replaceable |
| D-005 | 2026-08-20 | Auth, tenant, permission, CSRF, and BFF data remain unresolved placeholders | Accepted scope rule | Avoids inventing contracts before BFF/security work and preserves server authorization responsibility |
| D-006 | 2026-08-20 | Use a framework-light `fetch` transport placeholder with same-origin credentials and optional CSRF hook | Accepted at G1 | Centralizes browser transport without defining endpoints/envelopes or using public API keys |
| D-007 | 2026-08-20 | Do not fabricate/copy a package lock when npm registry is unavailable | Accepted integrity rule | Dependency-backed verification remains blocked until a real lockfile/install succeeds |
| D-008 | 2026-08-20 | EUI licensing/notice acceptance is an owner decision | Open owner/legal review | No legal compatibility conclusion is made |
| D-009 | 2026-08-20 | Stop after WP-02 at G1 | Satisfied | User supplied G1 architecture decisions on 2026-08-21; WP-03 may proceed, BFF still not started |
| D-010 | 2026-08-21 | Mount React under `${BASE_URL}ui/` while legacy PHP keeps existing routes | Accepted at G1 | Clear UI namespace; supports strangler migration and future server changes |
| D-011 | 2026-08-21 | Keep Apache as the current server target; document Nginx as a future deployment target | Accepted at G1 | Existing `.htaccess` owns compatibility routes; avoids combining frontend migration with web-server replacement |
| D-012 | 2026-08-21 | Model the React AppShell on the PHP information architecture but map responsibilities to EUI patterns | Accepted at G1 | Preserves user familiarity without copying legacy HTML/CSS; unmigrated modules remain legacy links |
| D-013 | 2026-08-21 | Default the React shell to Light mode | Accepted at G1 | First-run appearance must not silently follow OS dark preference; user preference can override later |
| D-014 | 2026-08-21 | Future browser auth remains PHP Session; 401 means unauthenticated, 403 means authenticated-but-forbidden; mutating UI requests use session-bound CSRF + SameSite + Origin validation | Direction accepted; exact contract deferred | Uses existing session hardening and fills the documented CSRF gap without mixing browser auth into `/api/v1` |
| D-015 | 2026-08-21 | Treat future SOC identity as level-1 and FreeITSM identity/tenant/role/capability state as level-2 behind an adapter/context boundary | Accepted at G1 | Allows SOC values to supply/supersede local values later without feature rewrites |
| D-016 | 2026-08-21 | Minimum React locale set: English, `zh-CN`, `zh-TW`; timezone independently configurable | Accepted at G1 | Locale must not imply timezone; reuse existing language sources where possible |
| D-017 | 2026-08-21 | Use hybrid API typing: generated transport DTO/enums + handwritten frontend domain/view models | Accepted at G1 | Catches contract drift without coupling UI state directly to server payload shape |
| D-018 | 2026-08-21 | Recommend Calendar as first post-security vertical slice, then Watchtower, then Tickets | Accepted planning decision | Calendar exercises CRUD/timezone/i18n/permission with less business complexity than Tickets |
| D-019 | 2026-08-21 | Use Playwright as primary E2E framework; Selenium is fallback only | Accepted at G1 | One framework covers Chromium/Firefox/WebKit, deep links, traces and local automation |
| D-020 | 2026-08-21 | Gate quality with static checks, coverage, automated accessibility, E2E/security-negative tests and user Human QoS | Accepted at G1 | Automated correctness and human usability are both required; no GitHub Actions dependency |
| D-021 | 2026-08-21 | Accept the generated project `package-lock.json` and successful `npm ci` as resolution of the dependency-install blocker | Accepted; install evidence only | This supersedes only the blocker state in D-002/D-007; it does not claim typecheck, lint, unit, build, Playwright, axe, Apache routing, Human QoS, or EUI legal acceptance |
| D-022 | 2026-08-21 | Treat Vite dev/preview route checks separately from production Apache `/ui/*` fallback | Accepted verification boundary | Vite can prove the client basename and SPA behavior; Apache fallback remains unimplemented and unverified until a narrowly reviewed server rule is tested |
| D-023 | 2026-08-21 | Freeze all feature-pilot execution until G2 reconciles D-018 Calendar-first with the historical WP-08 Watchtower / WP-24 Calendar schedule | Accepted planning hold; WP-04/05 unaffected | Avoids silently renumbering work packages or starting Calendar, Watchtower, or Tickets under contradictory plans; G2 must publish one coherent schedule before pilot work |
| D-024 | 2026-08-21 | Keep verification and the next PHP BFF packages local/manual with no GitHub Actions and no pull request; retain Go/go-zero and clustering as future-only | Accepted scope boundary | Prevents accidental upstream workflow/PR activity and avoids implementing the deferred backend vision during the PHP/React migration |
| D-025 | 2026-08-21 | Use `preview:test` as a local `/ui/` static-mount verification harness, while keeping Apache fallback as a separate future change | Accepted engineering correction | Vite preview served HTML for `/ui/assets/*`; the dedicated harness now verifies asset MIME and SPA fallback without editing PHP or pretending to validate Apache |

Decisions may be revised only with an explicit follow-up entry; do not silently rewrite accepted history.
The blocker wording retained in D-007 is historical after D-021; it remains in
the table only to preserve the original decision record.
