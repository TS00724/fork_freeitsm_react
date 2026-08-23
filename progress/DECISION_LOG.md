# FreeITSM React migration decision log

Decisions are append-only. A later change must add a superseding entry rather
than silently rewriting accepted history.

| ID | Date | Decision | Status | Rationale / consequence |
|---|---|---|---|---|
| D-001 | 2026-08-20 | Put all new frontend source in `frontend/`; build to `frontend/dist/` | Accepted | Prevents PHP/TSX mixing and keeps legacy PHP independently runnable |
| D-002 | 2026-08-20 | Pin React 18.3.1, EUI 119.0.0, Borealis 8.0.0, strict TypeScript and Vite | Accepted after lock/install review | No React 19 adoption |
| D-003 | 2026-08-20 | Use `BrowserRouter` and proposed `${BASE_URL}app/` prefix | Superseded by D-010 | Historical proposal |
| D-004 | 2026-08-20 | Provider order: runtime config → theme → locale/timezone → auth → tenant → permission → router | Accepted at G1 | Explicit replaceable boundaries |
| D-005 | 2026-08-20 | Auth, tenant, permission, CSRF and BFF data remain unresolved placeholders before security work | Accepted | Prevents invented contracts |
| D-006 | 2026-08-20 | Use a framework-light same-origin `fetch` transport placeholder | Accepted | No public API key or endpoint assumptions |
| D-007 | 2026-08-20 | Do not fabricate/copy a lockfile when registry access is unavailable | Accepted integrity rule; blocker later resolved | Preserves reproducibility |
| D-008 | 2026-08-20 | EUI licensing/notice acceptance is an owner decision | Accepted at G1 with notice obligations | Engineering review is not legal advice |
| D-009 | 2026-08-20 | Stop after WP-02 at G1 | Satisfied | G1 was reviewed and closed before BFF work |
| D-010 | 2026-08-21 | Mount React under `${BASE_URL}ui/`; legacy PHP keeps existing routes | Accepted | Clear strangler namespace |
| D-011 | 2026-08-21 | Apache is current; Nginx is future-compatible | Accepted | Avoids coupling UI migration to web-server replacement |
| D-012 | 2026-08-21 | Map PHP information architecture to EUI patterns | Accepted | Familiar shell without copying legacy HTML/CSS |
| D-013 | 2026-08-21 | Default React shell to Light | Accepted | Stable first-run appearance |
| D-014 | 2026-08-21 | Future browser auth uses PHP Session; 401/403 are distinct; writes use Session-bound CSRF + SameSite + Origin | Direction accepted; implementation WP-05 | Keeps browser auth separate from `/api/v1` |
| D-015 | 2026-08-21 | SOC level-1 identity maps through an adapter to FreeITSM level-2 context | Accepted | Future identity replacement without feature rewrites |
| D-016 | 2026-08-21 | Minimum locales: `en`, `zh-CN`, `zh-TW`; timezone independent | Accepted | Locale never implies timezone |
| D-017 | 2026-08-21 | Generated transport DTO/enums + handwritten frontend models | Accepted | Detects drift without coupling UI state to payloads |
| D-018 | 2026-08-21 | Recommend Calendar, then Watchtower, then Tickets after security foundation | Accepted planning decision | Feature execution remains frozen until G2 schedule reconciliation |
| D-019 | 2026-08-21 | Playwright primary E2E; Selenium fallback only | Accepted | Chromium/Firefox/WebKit, traces and deep links in one stack |
| D-020 | 2026-08-21 | Quality gate: static, coverage, a11y, E2E/security negatives and Human QoS | Accepted | Automated and human quality both matter |
| D-021 | 2026-08-21 | Accept real lockfile and successful `npm ci` as dependency blocker closure | Accepted | Does not imply browser automation passed |
| D-022 | 2026-08-21 | Vite preview checks are separate from production Apache `/ui/*` fallback | Accepted | Prevents false Apache claims |
| D-023 | 2026-08-21 | Freeze feature pilots until G2 resolves work-package order | Accepted | WP-04/05 unaffected |
| D-024 | 2026-08-21 | Local/manual verification only; no Actions, PR, Go/go-zero or clustering | Accepted | Repository and scope safety |
| D-025 | 2026-08-21 | Use `preview:test` only as a local static-mount harness | Accepted | Tests asset MIME/deep link without editing Apache root rules |
| D-026 | 2026-08-22 | Human QoS accepts `/ui/`, shell, Light/theme, provider structure and code readability | Accepted; G1 | Owner result `Pass` |
| D-027 | 2026-08-22 | EUI/Elastic terms accepted with LICENSE/NOTICE retention and future managed-service review | Accepted; G1 | Does not relicense third-party code |
| D-028 | 2026-08-22 | Defer Playwright/axe execution to a later Codex/script-capable environment without calling it Passed | Accepted verification debt | Owner explicitly removed it as a G1 blocker; debt remains visible |
| D-029 | 2026-08-22 | All future business routes default to lazy/dynamic imports; heavy components are feature/component chunks | Mandatory platform rule | Current 510.78 kB gzip chunk is not a future baseline |
| D-030 | 2026-08-22 | Close G1 and allow WP-04 only; stop again before WP-05 | Accepted | Preserves a separate security review |
| D-031 | 2026-08-22 | Create `/api/ui/v1` as a same-origin browser surface independent from Bearer `/api/v1` | Accepted WP-04 | No wildcard CORS, browser API key or machine bootstrap |
| D-032 | 2026-08-22 | Use one versioned success/error envelope with API version, request ID, correlation ID and UTC timestamp | Accepted WP-04 | Uniform transport and diagnostics |
| D-033 | 2026-08-22 | Validate incoming request/correlation IDs; generate UUID request IDs and default correlation to request ID | Accepted WP-04 | Prevents header injection while supporting trace propagation |
| D-034 | 2026-08-22 | Strictly reject unsafe paths, malformed/non-object JSON, unsupported media types and invalid typed route parameters | Accepted WP-04 | Fail-closed request boundary before business handlers |
| D-035 | 2026-08-22 | OpenAPI 3.1 is the UI transport source; dependency-free generation produces TypeScript DTOs/enums | Accepted WP-04 | Generated transport types remain separate from frontend domain/view models |
| D-036 | 2026-08-22 | WP-04 exposes only foundation and process-health routes; Session, CSRF, tenant, capability and DB binding remain unresolved | Accepted stop boundary | Prevents premature WP-05/security claims |
| D-037 | 2026-08-22 | Root `.htaccess`, PHP UI and existing `/api/v1` remain untouched; only a local UI API rewrite file is allowed | Accepted WP-04 | Limits regression surface |
| D-038 | 2026-08-23 | Version the `universal-code-writing` skill inside the repository and make `AGENTS.md` + its review checklist the default guiding/audit path for subsequent coding work | Accepted governance rule | Keeps Codex/GPT Pro implementation and review expectations explicit, reviewable and branch-versioned |
| D-039 | 2026-08-23 | Insert ADJ-001 before WP-05 to enforce responsibility/source-size governance and verify real `/ui/` initial-route gzip improvement | Accepted adjustment gate | Candidate lazy-split code must be measured with real `npm ci`/production build evidence before merge; WP-05 remains not started |
