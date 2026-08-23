# FreeITSM React/EUI Migration Progress

Repository: `TS00724/fork_freeitsm_react`  
Target: PHP UI → TypeScript + React 18 + EUI with a versioned PHP UI-BFF  
Program: 18 phases / 37 context-safe work packages / 7 user gates + explicit adjustment tasks  
Hard constraints: **No GitHub Actions, no Pull Request, no upstream write.**

## Progress rules

Effective progress is the minimum of Implementation, API/Contract, Verification,
and Docs/Handoff. `Verified complete` requires all four at 100%, Confidence Yes,
actual command evidence, no hidden blocker, no Actions and no upstream PR.

All post-WP-04 implementation/refactor/review work must use the repository-versioned
`universal-code-writing` guidance and audit path:

1. `AGENTS.md` — FreeITSM-specific instructions, source-size/responsibility limits, bundle policy and repository safety;
2. `skills/universal-code-writing/SKILL.md` — implementation/refactor/debug/test workflow;
3. `skills/universal-code-writing/references/language-profiles.md` — language/tool-specific guidance;
4. `skills/universal-code-writing/references/review-checklist.md` — mandatory substantial-patch audit;
5. the active WP/adjustment task and latest handoff.

A substantial patch is not complete merely because tests pass: its final review must also audit correctness, compatibility, security/privacy, maintainability and validation coverage against the repository checklist.

## Program summary

| Metric | Value |
|---|---:|
| Verified work packages | 4 / 37 |
| Current package | **ADJ-001 queued before WP-05 — source-size/responsibility + bundle governance** |
| G1 | Closed |
| Next implementation task | `progress/tasks/ADJ-001-source-size-and-bundle-governance.md` |
| Next normal work package | WP-05 only after ADJ-001 verified and user review |
| Next mandatory gate | G2 after WP-05 |
| GitHub Actions | Not used |
| Pull requests | None |
| Go/go-zero | Future only; not implemented |

## Work-package tracker

| WP / task | Phase | Scope | Status | Impl % | API % | Verify % | Docs % | Effective % | Confidence | Gate / next stop |
|---|---|---|---|---:|---:|---:|---:|---:|---|---|
| WP-01 | P00 | Baseline, inventory, route/API matrix and controls | Verified complete | 100 | 100 | 100 | 100 | 100 | Yes | Complete |
| WP-02 | P01 | Isolated React 18/TS/EUI/Vite scaffold | Verified complete | 100 | 100 | 100 | 100 | 100 | Yes | G1 closed; deferred browser debt retained |
| WP-03 | P02 | Architecture walkthrough, G1 decisions, ADRs and test strategy | Verified complete | 100 | 100 | 100 | 100 | 100 | Yes | G1 closed |
| WP-04 | P03 | `/api/ui/v1` front controller, route/envelope/OpenAPI/TS contract foundation | **Verified complete** | 100 | 100 | 100 | 100 | 100 | Yes | Complete; user requested governance adjustment before WP-05 |
| **ADJ-001** | Adjustment | Source-size/responsibility audit + real initial-route gzip budget + lazy splitting verification | **Queued** | 0 | 100 | 0 | 100 | 0 | No | Must run real `npm ci && npm run verify`; stop for user review before WP-05 |
| WP-05 | P03 | Session, CSRF, tenant, RBAC and object scope | Not started | 0 | 0 | 0 | 0 | 0 | No | Blocked by ADJ-001 review; G2 after completion |
| WP-06 | P04 | AppShell, routing, theme and i18n | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-07 | P04 | Notifications, search, files and streams | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-08 | P05 | Historical Watchtower pilot | Not started / frozen | 0 | 0 | 0 | 0 | 0 | No | G2 must reconcile pilot schedule |
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
| WP-19 | P10 | CMDB | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-20 | P10 | Network Mapper | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-21 | P10 | Process Mapper | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-22 | P11 | Contracts and Documents | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-23 | P11 | RFP Builder | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-24 | P12 | Calendar and Morning Checks | Not started / frozen | 0 | 0 | 0 | 0 | 0 | No | Recommended pilot but schedule deferred to G2 |
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
| WP-36 | P17 | SOC subsystem contract/future boundary | Not started | 0 | 0 | 0 | 0 | 0 | No | Contracts only; no Go work |
| WP-37 | P17 | Cutover, rollback and legacy retirement | Not started | 0 | 0 | 0 | 0 | 0 | No | G7 |

## Repository guiding / audit policy

The checked-in skill is the default guide for Codex/GPT Pro coding tasks; it is intentionally kept separate from project-specific policy:

- generic workflow source: `skills/universal-code-writing/SKILL.md`;
- language details: `skills/universal-code-writing/references/language-profiles.md`;
- final audit: `skills/universal-code-writing/references/review-checklist.md`;
- compact task prompt patterns: `skills/universal-code-writing/references/task-templates.md`;
- project-specific enforcement/table-of-contents: `AGENTS.md`.

Source-size limits are not permission for mechanical splitting. New/materially changed files must also remain responsibility-bounded; a file mixing transport, mapping, state and rendering can require splitting below the numeric limit. Generated code and untouched legacy PHP are excluded from the new-code LOC policy.

## WP-04 delivered boundary

- one executable `/api/ui/v1/index.php` front controller;
- declarative foundation route table with `GET/HEAD/OPTIONS /` and `/health`;
- strict method, path, `Content-Type`, JSON-object and typed-parameter parsing;
- versioned success/error envelopes and 400/401/403/404/405/409/415/422/429/500 semantics;
- validated/generated request and correlation IDs in headers and bodies;
- unresolved server context slots for actor, tenant/company, capabilities, locale and timezone;
- OpenAPI 3.1 source plus dependency-free TypeScript transport generation/check;
- PHP syntax and 36 contract/security-negative tests;
- full frontend `npm ci` and `npm run verify` gate with generated-contract drift check;
- no Session, CSRF, tenant/RBAC/object-scope enforcement, DB access or business routes.

## Retained debts and current stop

Playwright Chromium/Firefox/WebKit and axe remain explicitly deferred by the G1 owner decision and are **not** recorded as Passed.

The last verified React foundation baseline remains **1,641.07 kB minified / 510.78 kB gzip** for the previous single main chunk. A candidate branch `perf-main-chunk-lazy-split` exists, but it has not been accepted into `main` because the current environment has not produced the required real `npm ci` + production-build gzip evidence. ADJ-001 owns that verification and any necessary follow-up splitting.

Production Apache `/ui/*` SPA fallback is still a separate task. WP-04 added only the narrow `/api/ui/v1/.htaccess` rewrite and did not modify root `.htaccess`.

**Current stop: execute ADJ-001 only. Do not start WP-05 until ADJ-001 has real source-size/bundle evidence, a completed universal-code-writing review audit, updated handoff/progress, and explicit user review.**
