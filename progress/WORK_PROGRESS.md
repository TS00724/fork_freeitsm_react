# FreeITSM React/EUI Migration Progress

Repository: `TS00724/fork_freeitsm_react`  
Target: PHP UI → TypeScript + React 18 + EUI, with a future PHP UI-BFF  
Program: 18 phases / 37 context-safe work packages / 7 user gates  
Hard constraint: **Do not create, run, edit, or depend on GitHub Actions.**

## Progress rules

Effective progress is the minimum of Implementation, API/Contract, Verification,
and Docs/Handoff. `Verified complete` requires all four at 100%, Confidence Yes,
actual command evidence, no hidden blocker, no Actions, and no upstream PR.

## Program summary

| Metric | Value |
|---|---:|
| Verified work packages | 0 / 37 |
| Current package | G1 review |
| Next mandatory gate | G1 — waiting for user |
| GitHub Actions | Not used |
| Pull requests | None |
| Go/go-zero | Out of scope |

## Work-package tracker

| WP | Phase | Scope | Status | Impl % | API % | Verify % | Docs % | Effective % | Confidence | Gate / next stop |
|---|---|---|---|---:|---:|---:|---:|---:|---|---|
| WP-01 | P00 | Baseline, inventory, route/API matrix and control files | In progress | 100 | 100 | 90 | 100 | 90 | No | Literal target worktree/status remains unavailable |
| WP-02 | P01 | Isolated React 18/TS/EUI/Vite scaffold | Blocked | 95 | 100 | 35 | 100 | 35 | No | **Stopped at G1; restore npm and verify before 100%** |
| WP-03 | P02 | User architecture walkthrough and ADRs | Not started | 0 | 0 | 0 | 0 | 0 | No | Enter only after written G1 approval |
| WP-04 | P03 | `/api/ui/v1` BFF front controller and contracts | Not started | 0 | 0 | 0 | 0 | 0 | No | Not authorized |
| WP-05 | P03 | Session, CSRF, tenant, RBAC and object scope | Not started | 0 | 0 | 0 | 0 | 0 | No | Not authorized; G2 later |
| WP-06 | P04 | AppShell, routing, theme and i18n | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-07 | P04 | Notifications, search, files and streams | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
| WP-08 | P05 | Watchtower pilot vertical slice | Not started | 0 | 0 | 0 | 0 | 0 | No | G3 |
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
| WP-24 | P12 | Calendar and Morning Checks | Not started | 0 | 0 | 0 | 0 | 0 | No | Future |
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

## WP-01/WP-02 control fields

| WP | Start SHA | End SHA | Blocker | Next step |
|---|---|---|---|---|
| WP-01 | `bfad6b0db7242686114143cc590a146871a44b21` | `b703183176db6c4ff56b6860725220cf8914d1fa` | No mounted target worktree for literal status/uncommitted-state verification | User may repeat baseline commands in a real clone; keep all writes non-forced |
| WP-02 | `b703183176db6c4ff56b6860725220cf8914d1fa` | WP-02 implementation commit; updated after creation | npm registry DNS, no real lockfile, dependency-backed checks unavailable | Generate/review lockfile, run local verification, then perform G1 code review |

## Gate status

G1 is **Waiting for user review**. WP-03, BFF, session/CSRF/RBAC, Watchtower,
Tickets, and every other business module remain not started.

**已停止在 G1，等待用户审核，尚未开始 BFF。**
