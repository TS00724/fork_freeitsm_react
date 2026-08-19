# FreeITSM React/EUI Migration Progress

Repository: `TS00724/fork_freeitsm_react`  
Target: PHP UI → TypeScript + React 18 + EUI, with a PHP UI-BFF  
Current program: 18 phases / 37 context-safe work packages / 7 user gates  
Hard constraint: **Do not create, run, edit, or depend on GitHub Actions.**

## GPT Pro update protocol

At the start of every work period:

1. Read `progress/MIGRATION_MASTER_PLAN.md` and this file completely.
2. Read the latest applicable `handoffs/WP-XX.md`, verification report, decision log and API matrix.
3. Confirm the current branch/SHA and protect any unrelated or uncommitted work.
4. Work on only one `WP-XX`, unless the master plan explicitly pairs packages.
5. Never cross a mandatory gate without the user's written approval.

At the end of every work period:

1. Update exactly one row below with real percentages and evidence.
2. Effective progress is the minimum of Implementation, API/Contract, Verification and Docs/Handoff.
3. Mark `Verified complete` only when all four values are 100%, confidence is `Yes`, and actual commands/results are recorded.
4. Record start/end SHA, files read, files changed, routes/contracts, tests, blockers and the next 5–10 files in `handoffs/WP-XX.md`.
5. If blocked, state the exact cause and unblock condition; never fabricate credentials, provider success or test results.
6. Confirm `.github/workflows/` was not created or modified.

Allowed status values: `Not started`, `In progress`, `Blocked`, `Verified complete`.

## Program summary

| Metric | Value |
|---|---:|
| Overall effective progress | 0% |
| Verified work packages | 0 / 37 |
| Current package | WP-01 |
| Next mandatory gate | G1 after WP-02 |
| Go-zero implementation | Out of scope |

## Work-package tracker

| WP | Phase | Scope | Status | Impl % | API % | Verify % | Docs % | Confidence | Gate / next stop |
|---|---|---|---|---:|---:|---:|---:|---|---|
| WP-01 | P00 | Baseline, inventory, route/API matrix and control files | Not started | 0 | 0 | 0 | 0 | No | Proceed to WP-02; stop if earlier scaffold is found |
| WP-02 | P01 | Isolated React 18/TS/EUI/Vite scaffold | Not started | 0 | 0 | 0 | 0 | No | **Stop at G1; do not start BFF** |
| WP-03 | P02 | User architecture walkthrough and ADRs | Not started | 0 | 0 | 0 | 0 | No | Enter WP-04 only after G1 approval |
| WP-04 | P03 | `/api/ui/v1` BFF front controller and contracts | Not started | 0 | 0 | 0 | 0 | No | Proceed after contract stability |
| WP-05 | P03 | Session, CSRF, tenant, RBAC and object scope | Not started | 0 | 0 | 0 | 0 | No | G2; no modules before approval |
| WP-06 | P04 | AppShell, routing, theme and i18n | Not started | 0 | 0 | 0 | 0 | No | Proceed to shared services |
| WP-07 | P04 | Notifications, search, files and streams | Not started | 0 | 0 | 0 | 0 | No | Proceed to pilot |
| WP-08 | P05 | Watchtower pilot vertical slice | Not started | 0 | 0 | 0 | 0 | No | G3; approve or repair pilot |
| WP-09 | P06 | Tickets I: list, filters, detail and thread | Not started | 0 | 0 | 0 | 0 | No | Proceed to WP-10 |
| WP-10 | P06 | Tickets II: create, reply, attachments and notes | Not started | 0 | 0 | 0 | 0 | No | Proceed to WP-11 |
| WP-11 | P06 | Tickets III: assignment, bulk, SLA and presence | Not started | 0 | 0 | 0 | 0 | No | Proceed to WP-12 |
| WP-12 | P06 | Tickets IV: merge/split, triage, dashboard/settings | Not started | 0 | 0 | 0 | 0 | No | G4 full Tickets review |
| WP-13 | P07 | Assets I: inventory and custody | Not started | 0 | 0 | 0 | 0 | No | Proceed to WP-14 |
| WP-14 | P07 | Assets II: dashboard, labels, scan and integrations | Not started | 0 | 0 | 0 | 0 | No | Proceed to Knowledge |
| WP-15 | P08 | Knowledge | Not started | 0 | 0 | 0 | 0 | No | Proceed to Tasks |
| WP-16 | P08 | Tasks | Not started | 0 | 0 | 0 | 0 | No | Proceed to Change |
| WP-17 | P09 | Change Management | Not started | 0 | 0 | 0 | 0 | No | Proceed to Problem |
| WP-18 | P09 | Problem Management | Not started | 0 | 0 | 0 | 0 | No | Proceed to CMDB |
| WP-19 | P10 | CMDB | Not started | 0 | 0 | 0 | 0 | No | Proceed to Network Mapper |
| WP-20 | P10 | Network Mapper | Not started | 0 | 0 | 0 | 0 | No | Proceed to Process Mapper |
| WP-21 | P10 | Process Mapper | Not started | 0 | 0 | 0 | 0 | No | Proceed to Contracts |
| WP-22 | P11 | Contracts and Documents | Not started | 0 | 0 | 0 | 0 | No | Proceed to RFP |
| WP-23 | P11 | RFP Builder | Not started | 0 | 0 | 0 | 0 | No | Proceed to operations |
| WP-24 | P12 | Calendar and Morning Checks | Not started | 0 | 0 | 0 | 0 | No | Proceed to WP-25 |
| WP-25 | P12 | Service Status, Software and Reporting/Intune | Not started | 0 | 0 | 0 | 0 | No | Split only if scope limit is exceeded |
| WP-26 | P13 | Workflow | Not started | 0 | 0 | 0 | 0 | No | Proceed to Forms |
| WP-27 | P13 | Forms | Not started | 0 | 0 | 0 | 0 | No | Proceed to LMS |
| WP-28 | P13 | LMS | Not started | 0 | 0 | 0 | 0 | No | Proceed to messaging |
| WP-29 | P14 | Messaging and integrations | Not started | 0 | 0 | 0 | 0 | No | Proceed to War Room |
| WP-30 | P14 | War Room | Not started | 0 | 0 | 0 | 0 | No | Proceed to System Admin |
| WP-31 | P15 | System Admin I: people and access | Not started | 0 | 0 | 0 | 0 | No | Proceed to WP-32 |
| WP-32 | P15 | System Admin II: auth, security and operations | Not started | 0 | 0 | 0 | 0 | No | G5 sensitive-operation review |
| WP-33 | P16 | Self-Service | Not started | 0 | 0 | 0 | 0 | No | Proceed to external surfaces |
| WP-34 | P16 | Webchat, System Wiki and browser extension | Not started | 0 | 0 | 0 | 0 | No | G6 identity-boundary review |
| WP-35 | P17 | System-wide hardening and parity | Not started | 0 | 0 | 0 | 0 | No | No cutover with open blockers |
| WP-36 | P17 | SOC subsystem contract and future boundary | Not started | 0 | 0 | 0 | 0 | No | Contracts only; no Go/go-zero work |
| WP-37 | P17 | Cutover, rollback and legacy retirement | Not started | 0 | 0 | 0 | 0 | No | G7 final user acceptance |

## User gate status

| Gate | Required after | Status | Approval / decisions |
|---|---|---|---|
| G1 | WP-02 React framework | Pending | User reads the entire frontend framework before BFF |
| G2 | WP-05 BFF/security | Pending | Session, CSRF, tenant, RBAC and object scope |
| G3 | WP-08 pilot | Pending | UI/module pattern approval |
| G4 | WP-12 Tickets | Pending | Core parity and architecture stability |
| G5 | WP-32 System Admin | Pending | Secrets, permissions and destructive actions |
| G6 | WP-34 external surfaces | Pending | Analyst/portal identity isolation |
| G7 | WP-37 cutover | Pending | Default route, rollback and deletion approval |

## Current next action

Execute WP-01 and WP-02 only. After WP-02, provide the user with a runnable preview, code-reading order, unresolved architecture decisions and verification evidence, then stop at G1.

