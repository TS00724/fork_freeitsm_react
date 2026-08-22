# FreeITSM React/EUI Migration Progress

Repository: `TS00724/fork_freeitsm_react`  
Target: PHP UI → TypeScript + React 18 + EUI, with a future PHP UI-BFF  
Program: 18 phases / 37 context-safe work packages / 7 user gates  
Hard constraint: **Do not create, run, edit, or depend on GitHub Actions.**

## Progress rules

Effective progress is the minimum of Implementation, API/Contract, Verification,
and Docs/Handoff. `Verified complete` requires all four at 100%, Confidence Yes,
truthful command/decision evidence, no hidden blocker, no Actions, and no upstream
PR. A verification item may be deferred only by an explicit owner decision
recorded in `progress/DECISION_LOG.md`; a deferred item remains open evidence debt
and must never be relabeled as passed.

## Program summary

| Metric | Value |
|---|---:|
| Verified work packages | **3 / 37** |
| Current package | **G1 closed; WP-04 not started** |
| G1 | **Closed on 2026-08-22 by explicit owner decision** |
| Next work package | WP-04 — `/api/ui/v1` BFF front controller and contracts |
| Next mandatory gate | G2 after WP-05 |
| GitHub Actions | Not used |
| Pull requests | None |
| Go/go-zero | Future only; not implemented |

## Work-package tracker

| WP | Phase | Scope | Status | Impl % | API % | Verify % | Docs % | Effective % | Confidence | Gate / next stop |
|---|---|---|---|---:|---:|---:|---:|---:|---|---|
| WP-01 | P00 | Baseline, inventory, route/API matrix and control files | Verified complete | 100 | 100 | 100 | 100 | 100 | Yes | Closed |
| WP-02 | P01 | Isolated React 18/TS/EUI/Vite scaffold | **Verified complete** | 100 | 100 | 100 | 100 | 100 | Yes | G1 closed; browser matrix deferred by owner, not passed |
| WP-03 | P02 | User architecture walkthrough, G1 decisions, ADRs and E2E strategy | **Verified complete** | 100 | 100 | 100 | 100 | 100 | Yes | G1 closed; lazy/code-splitting is now mandatory platform policy |
| WP-04 | P03 | `/api/ui/v1` BFF front controller and contracts | Not started | 0 | 0 | 0 | 0 | 0 | No | Next work period only |
| WP-05 | P03 | Session, CSRF, tenant, RBAC and object scope | Not started | 0 | 0 | 0 | 0 | 0 | No | G2 after completion |
| WP-06 | P04 | AppShell, routing, theme and i18n | Not started | 0 | 0 | 0 | 0 | 0 | No | Must follow G1 lazy-loading policy |
| WP-07 | P04 | Notifications, search, files and streams | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-08 | P05 | Watchtower pilot vertical slice | Not started | 0 | 0 | 0 | 0 | 0 | No | G3; pilot order still frozen until G2 reconciles plan |
| WP-09 | P06 | Tickets I | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-10 | P06 | Tickets II | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-11 | P06 | Tickets III | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-12 | P06 | Tickets IV | Not started | 0 | 0 | 0 | 0 | 0 | No | G4 |
| WP-13 | P07 | Assets I | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-14 | P07 | Assets II | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-15 | P08 | Knowledge | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-16 | P08 | Tasks | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-17 | P09 | Change Management | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-18 | P09 | Problem Management | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-19 | P10 | CMDB | Not started | 0 | 0 | 0 | 0 | 0 | No | Future; heavy mapper dependencies must be lazy-loaded |
| WP-20 | P10 | Network Mapper | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-21 | P10 | Process Mapper | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-22 | P11 | Contracts and Documents | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-23 | P11 | RFP Builder | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-24 | P12 | Calendar and Morning Checks | Not started | 0 | 0 | 0 | 0 | 0 | No | Calendar remains recommended first future vertical slice after security foundation |
| WP-25 | P12 | Service Status, Software and Reporting/Intune | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-26 | P13 | Workflow | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-27 | P13 | Forms | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-28 | P13 | LMS | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-29 | P14 | Messaging and integrations | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-30 | P14 | War Room | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-31 | P15 | System Admin I | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-32 | P15 | System Admin II | Not started | 0 | 0 | 0 | 0 | 0 | No | G5 |
| WP-33 | P16 | Self-Service | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-34 | P16 | Webchat, System Wiki and browser extension | Not started | 0 | 0 | 0 | 0 | 0 | No | G6 |
| WP-35 | P17 | System-wide hardening and parity | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-36 | P17 | SOC subsystem contract and future boundary | Not started | 0 | 0 | 0 | 0 | 0 | No | Contracts only; no Go work |
| WP-37 | P17 | Cutover, rollback and legacy retirement | Not started | 0 | 0 | 0 | 0 | 0 | No | G7 |

## G1 accepted decisions

- React prefix: `${BASE_URL}ui/`;
- PHP/React parallel strangler migration;
- Apache current; Nginx future-compatible;
- PHP information architecture mapped to EUI shell patterns;
- default Light theme;
- future PHP Session + 401/403 + session-bound CSRF + Origin strategy;
- SOC level-1 identity → FreeITSM level-2 adapter/context;
- English + `zh-CN` + `zh-TW`; timezone separately configurable;
- hybrid OpenAPI/generated DTO + handwritten frontend model;
- Calendar recommended first future vertical slice; Watchtower next; Tickets later,
  while final pilot scheduling remains frozen until G2 reconciles historical plan;
- Playwright primary E2E; Selenium fallback;
- coverage + a11y + E2E/security negatives + Human QoS;
- EUI/Elastic terms accepted by project owner with LICENSE/NOTICE preservation and
  future managed-service/distribution re-review;
- Human QoS: Pass;
- **mandatory lazy/code-splitting policy:** business routes default to dynamic
  import/lazy loading, AppShell keeps only startup-critical synchronous code, and
  heavy feature-only dependencies are split where practical;
- current 1,641.07 kB minified / **510.78 kB gzip** main chunk remains visible
  technical debt and is not a future bundle-size baseline;
- pinned Chromium/Firefox/WebKit/axe execution is explicitly deferred to later
  Codex/script verification and is **not recorded as passed**.

## Current stop

G1 is closed. WP-01/WP-02/WP-03 are verified complete for their revised G1
scope. Non-browser automation remains green at implementation source SHA
`46c901597557abe7f319a880c9a3539105307196`: reproducible install, zero currently
reported lockfile vulnerabilities, typecheck, lint, 43 tests, coverage,
production build, and `/ui/` asset/deep-link preview probe passed in the recorded
environment.

Browser automation is deferred verification debt. Production Apache `/ui/*`
fallback remains a separate future server change. **WP-04, BFF,
Session/CSRF/RBAC server implementation, Calendar, Watchtower, Tickets and all
other business modules remain not started in the G1 closure work period.**

The next GPT Pro context must start from the final merged G1 closure commit, read
this file plus the decision/verification/handoff documents, and then execute
WP-04 only.
