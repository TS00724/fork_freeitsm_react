# FreeITSM React migration verification report

Report date: 2026-08-20  
Target repository: `TS00724/fork_freeitsm_react`  
Program start SHA: `bfad6b0db7242686114143cc590a146871a44b21`

## WP-01 — repository baseline and controls

| Check | Result | Evidence |
|---|---|---|
| Repository identity and push permission | Pass | Repository metadata identifies only the target fork and exact clone URL |
| Current branch/SHA | Pass | Remote `main` at the start SHA |
| Visible branches | Pass | Only `main` returned |
| Master plan/progress read | Pass | Both existing progress files read completely |
| Existing frontend/manifests | Pass | No `frontend/`, React/Vite/TSX manifest or lockfile found |
| API/session/routing boundaries | Pass | Required PHP/API/security/service paths inspected |
| `.github/workflows/` absence | Pass | `.github` contains only Issue Template |
| Local `git status --short` against target | Not run | No mounted target checkout is exposed by the environment |
| Independent full extension recount | Not run | Remote tree inspected; source-audit counts retained and labeled |

Result: Implementation 100%, API/Contract 100%, Verification 90%, Docs/Handoff
100%; effective 90%, Confidence **No**, status **In progress**. Publication must
re-read `main` and use a non-forced fast-forward.

## WP-02 — isolated React/EUI foundation

### Actual commands

| Command/check | Exit | Classification | Result |
|---|---:|---|---|
| `npm run verify:structure` | 0 | Applicable | Passed |
| `npm run verify:isolation` | 0 | Applicable | Passed |
| `npm run verify:lockfile` | 1 | Blocked | No lockfile |
| `npm ci --ignore-scripts --fetch-retries=0 --fetch-timeout=3000` | 1 | Blocked | npm requires lockfile |
| `npm run typecheck` | 1 | Blocked | Missing dependency type definitions |
| `npm run lint` | 127 | Blocked | ESLint not installed |
| `npm run test` | 127 | Blocked | Vitest not installed |
| `npm run build` | 1 | Blocked | TypeScript stops on missing types |
| `npm run dev -- --host 127.0.0.1` | 127 | Blocked | Vite not installed |
| disposable lockfile probe | 124 | Blocked | npm registry `EAI_AGAIN`, then timeout |
| dependency-free runtime/API assertions | 0 | Supplemental | Passed |
| early runtime `<base>` assertion | 0 | Supplemental | Passed for `/freeitsm-app/app/` |
| static scope/isolation assertions | 0 | Applicable | Passed |

### Required behavior status

| Requirement | Status | Evidence/limit |
|---|---|---|
| React entry can start | Not verified | Vite dependency unavailable |
| AppShell renders | Test authored, not executed | Vitest unavailable |
| Direct subroute with BASE_URL | Logic passed; render not verified | Runtime basename/base assertions passed; component test blocked |
| 404 reachable | Test authored, not executed | Vitest unavailable |
| Light/dark theme | Implemented; test authored, not executed | Dependency test blocked |
| PHP entry unaffected | Pass by scope | Zero PHP changes |
| Build output separated | Pass by config/static check | `frontend/dist/`; no PHP target |
| No Actions addition/change | Pass | No workflow path in patch |
| No business module migration | Pass | `features/` contains convention README only |
| No BFF implementation | Pass | No `api/ui/v1` path or PHP handler |

### Result

Implementation 95%, API/Contract 100% for the intentionally contract-free
placeholder, Verification 35%, Docs/Handoff 100%. Effective progress is **35%**,
Confidence **No**, status **Blocked**. The hidden blocker is not waived: a real
lockfile, dependency install, full typecheck/lint/test/build/start, and manual
runtime review are still required.

## Publication checks still required

Before moving `main`: re-read its SHA, confirm exact repository URL, compare all
changed paths, confirm no PHP/workflow/BFF/business paths, then update the ref
with `force=false`. Literal local `git status` and `git diff --check` remain
unavailable because no target checkout is mounted; this limitation is not
misreported as a pass.

## Actions and PR confirmation

No GitHub Actions workflow was created, modified, executed, or depended upon.
No pull request was created or sent upstream.
