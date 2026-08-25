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
| Current package | **WP-05 authorized to start; ADJ-001 Phase B remains deferred verification debt** |
| G1 | Closed |
| Owner decision | ADJ-001 Phase A accepted with high remediation confidence; real npm/Vite measurement deferred |
| Current implementation task | WP-05 Session/CSRF/tenant/RBAC/object-scope security foundation |
| Mandatory deferred task | ADJ-001 Phase B must be completed before G2 closure or production release, whichever occurs first |
| Next mandatory gate | G2 after WP-05 plus ADJ-001 Phase B evidence |
| GitHub Actions | Not used |
| Pull requests | None |
| Go/go-zero | Future only; not implemented |

## Work-package tracker

| WP / task | Phase | Scope | Status | Impl % | API % | Verify % | Docs % | Effective % | Confidence | Gate / next stop |
|---|---|---|---|---:|---:|---:|---:|---:|---|---|
| WP-01 | P00 | Baseline, inventory, route/API matrix and controls | Verified complete | 100 | 100 | 100 | 100 | 100 | Yes | Complete |
| WP-02 | P01 | Isolated React 18/TS/EUI/Vite scaffold | Verified complete | 100 | 100 | 100 | 100 | 100 | Yes | G1 closed; deferred browser debt retained |
| WP-03 | P02 | Architecture walkthrough, G1 decisions, ADRs and test strategy | Verified complete | 100 | 100 | 100 | 100 | 100 | Yes | G1 closed |
| WP-04 | P03 | `/api/ui/v1` front controller, route/envelope/OpenAPI/TS contract foundation | Verified complete | 100 | 100 | 100 | 100 | 100 | Yes | Complete |
| **ADJ-001** | Adjustment | Source-size/responsibility audit + initial-route gzip governance + lazy splitting | **Phase A owner-accepted; Phase B deferred** | 100 | 100 | 60 | 100 | 60 | **Yes — remediation direction; runtime measurement unverified** | Does not block WP-05; real gzip/forward-budget/full frontend gate required before G2 or production |
| **WP-05** | P03 | Session, CSRF, tenant, RBAC and object scope | **Authorized to start** | 0 | 0 | 0 | 0 | 0 | No | Implement security foundation only; G2 after completion and deferred-debt review |
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

## ADJ-001 owner decision and confidence record — 2026-08-25

The project owner explicitly authorizes ADJ-001 Phase B runtime verification to
be executed later and authorizes WP-05 to start after the Phase A implementation
is integrated. This is a gate change, not a fabricated test result.

ADJ-001 is **not** `Verified complete`. The following remain unclaimed:

- clean `npm ci` on the ADJ tree;
- full TypeScript/lint/Vitest/Vite verification;
- actual `/ui/` initial-route gzip value;
- measured forward bundle budget;
- final `npm run verify` with that budget.

Technical confidence that the implementation can reduce the `/ui/` initial-route
payload below the historical 510,780-byte gzip baseline is **high**, for these
specific reasons:

1. all five foundation page modules have moved from synchronous imports to
   explicit route-level dynamic imports;
2. the default Home route no longer imports `EuiCodeBlock`, removing the
   Prism/Refractor code-highlighting path from the route source;
3. AppShell remains the only synchronous route composition boundary;
4. the bundle analyzer measures the de-duplicated entry + static imports +
   default-route lazy/static closure, so moving bytes between filenames cannot
   create a false pass;
5. the future gate will fail unless the measured initial route is below 510,780
   bytes and a measured forward budget is committed.

This is an engineering-confidence assessment, **not production-build evidence**.
The 510,780-byte value remains the last verified baseline until Phase B runs.

Phase B is mandatory before either:

- G2 is closed; or
- any React UI production release is approved,

whichever occurs first. If Phase B measures `>= 510780`, the debt reopens as a
blocking adjustment and further measured splitting is required.

## Repository guiding / audit policy

The checked-in skill is the default guide for Codex/GPT Pro coding tasks:

- generic workflow: `skills/universal-code-writing/SKILL.md`;
- language details: `skills/universal-code-writing/references/language-profiles.md`;
- final audit: `skills/universal-code-writing/references/review-checklist.md`;
- project-specific enforcement: `AGENTS.md`.

Source-size limits are not permission for mechanical splitting. New/materially
changed files must also remain responsibility-bounded. Generated code and
untouched legacy PHP are excluded from the new-code LOC policy.

## Retained debts

- ADJ-001 Phase B: real npm/Vite bundle measurement and forward budget;
- Playwright Chromium/Firefox/WebKit and axe: deferred, not Passed;
- production Apache `/ui/*` SPA fallback: unimplemented;
- historical bundle baseline: **1,641.07 kB minified / 510.78 kB gzip** until
  superseded by real Phase B evidence.

**Current boundary: WP-05 may start. Business features, WP-06 and later work
remain frozen.**
