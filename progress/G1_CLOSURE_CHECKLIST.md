# G1 closure checklist — user + GPT Pro

- Reviewed implementation source: `46c901597557abe7f319a880c9a3539105307196`
- Closure baseline: `132d77e2e7ea88910bee3fa45819ac7e18c635ec`
- Closure decision date: 2026-08-22
- Status: **Closed by explicit owner decision**
- Rule: browser automation remains open evidence debt and must not be described as passed; WP-04 may only begin in a new work period/prompt.

## 1. Browser automation

The intended browser gate remains:

```bash
cd frontend
npm ci
npx playwright install
npm run test:e2e
npm run test:a11y
```

| Field | Value |
|---|---|
| Date / operator | 2026-08-22 / GPT Pro attempt; final rerun deferred to later Codex/script execution |
| Node / npm | Attempt environment recorded in `progress/verification/WP-03-g1-runtime-attempt-2026-08-22.md` |
| Chromium | **Deferred; not passed** |
| Firefox | **Deferred; not passed** |
| WebKit | **Deferred; not passed** |
| axe serious/critical | **Deferred; not passed** |
| Evidence path or pasted summary | `progress/verification/WP-03-g1-runtime-attempt-2026-08-22.md` |

The project owner explicitly changed the G1 closure policy on 2026-08-22: the
pinned Playwright/axe browser matrix is deferred to a later Codex/script-enabled
environment and no longer blocks G1 closure. This is a deferment, not a test
pass. Future verification must preserve the original commands, exit codes,
traces/screenshots where applicable, and must not overwrite the historical
blocked evidence.

## 2. User Human QoS

The owner reviewed the G1 architecture and accepted the following decisions:

| Area | Result | Notes |
|---|---|---|
| A — AppShell and `/ui/` architecture | Pass | React/EUI shell, `/ui/` prefix and PHP/React strangler direction accepted |
| B — theme and interaction foundation | Pass | Light default, theme toggle and current 403/404/error foundation accepted |
| C — bundle/performance architecture | Pass with mandatory follow-up | Current 1,641.07 kB minified / 510.78 kB gzip main chunk is accepted for G1 only; it is not a future bundle-size baseline |
| D — code/provider maintainability | Pass | RuntimeConfig → theme → locale/timezone → auth → tenant → permission → router boundaries accepted |

| Field | Value |
|---|---|
| Reviewer / date | Project owner / 2026-08-22 |
| Result (`Pass`, `Needs tuning`, `Block`) | **Pass** |
| Requested adjustments | Make lazy loading/code splitting a platform requirement from G1 onward |

### Mandatory lazy-loading constraint

From the first post-G1 implementation work onward:

- business routes must default to `React.lazy()` / dynamic `import()` or an
  equivalent route-level lazy mechanism;
- AppShell may synchronously load only startup-critical platform code;
- heavy components such as DataGrid, charts, editors, CodeBlock/Prism,
  CMDB/Mapper/diagramming components and similar feature-only dependencies must
  be split at feature/component level where practical;
- Calendar, Watchtower, Tickets, CMDB and other business modules must not be
  allowed to accumulate into the initial AppShell chunk by default;
- later Codex/script verification should add bundle analysis/treemap evidence so
  module-level size contributions can be measured instead of guessed.

The current 510.78 kB gzip main chunk remains an explicit known risk and must not
be hidden or silently waived.

## 3. EUI/Elastic license decision

The owner reviewed the engineering inventory in
`docs/react-migration/THIRD_PARTY_REVIEW.md` and accepts the current EUI/Elastic
terms for continuation of this project.

| Field | Value |
|---|---|
| Owner/legal reviewer / date | Project owner / 2026-08-22 |
| Intended deployment | Not fixed at G1; current acceptance permits continued project development, while future managed-service/distribution plans require release-time review |
| Result (`Accepted`, `Rejected`, `Needs legal review`) | **Accepted** |
| Conditions / notice requirements | Preserve applicable third-party LICENSE/NOTICE material; re-review if deployment model changes, especially managed-service use |

This is the owner's project decision and does not convert the engineering review
into legal advice or relicense Elastic dependencies under the FreeITSM MIT
license.

## 4. Final G1 release decision

G1 is **Closed** on 2026-08-22 under the revised owner-approved closure policy.
WP-02 and WP-03 may be recorded as `Verified complete` for their G1 scope because
all currently required closure decisions are complete and the browser matrix has
been explicitly deferred rather than misreported as passing.

The deferred Playwright/axe matrix remains mandatory verification debt for a
later Codex/script-enabled work period. Any future failure must be fixed and
recorded; G1 closure is not permission to ignore browser regressions.

The following remain outside this G1 closure and are still unimplemented:

- production Apache `/ui/*` fallback and legacy-route regression validation;
- `/api/ui/v1` BFF implementation;
- Session/CSRF/tenant/RBAC/object-scope server enforcement;
- Calendar, Watchtower, Tickets and all other business features;
- Go/go-zero, clustering and SOC runtime implementation.

No GitHub Actions or pull request is part of this closure. The next work period
may begin WP-04 only from the final merged G1 closure commit and must re-read the
progress/decision/handoff files first.
