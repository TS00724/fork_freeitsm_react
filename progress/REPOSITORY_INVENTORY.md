# FreeITSM repository inventory — WP-01

Inventory date: 2026-08-20  
Repository: `TS00724/fork_freeitsm_react`  
Start branch: `main`  
Start commit: `bfad6b0db7242686114143cc590a146871a44b21`  
Start tree: `a9f1d3e1d93f378d797f77360c7bd054206ac47c`

## Repository and Git safety baseline

| Check | Result |
|---|---|
| Repository metadata | `TS00724/fork_freeitsm_react`, public, push permission available |
| Exact clone/push target | `https://github.com/TS00724/fork_freeitsm_react.git` |
| Default/current branch | `main` |
| Visible branches | `main` only |
| Current remote HEAD | `bfad6b0db7242686114143cc590a146871a44b21` |
| Branch protection | Not enabled at the inspected baseline |
| Pull requests created | None |
| GitHub Actions | `.github` contains only `ISSUE_TEMPLATE`; `.github/workflows/` is absent |

The execution environment did not provide a mounted Git checkout and could not
resolve GitHub/npm from its shell. Therefore the literal local commands
`git status --short`, `git branch --show-current`, `git rev-parse HEAD`, and
`git remote -v` could not be run against the target worktree. Their remote
repository equivalents were obtained through the GitHub repository/branch/tree
interfaces and recorded in `progress/verification/WP-01-repository-access.txt`.
No claim is made about uncommitted changes in a user's separate local clone.
Publication will use a non-forced, fast-forward ref update only after re-reading
`main`, which prevents overwriting a newly published remote commit.

## Files read completely or inspected at the baseline

- `progress/MIGRATION_MASTER_PLAN.md`
- `progress/WORK_PROGRESS.md`
- root `README.md`, `.gitignore`, `.dockerignore`, `.htaccess`, `Dockerfile`, `docker-compose.yml`, `config.php`, and `index.php`
- `api/v1/index.php` and `api/v1/lib/routes.php`
- `includes/request_guard.php`, `includes/session_security.php`, and the `includes/services/` directory
- root recursive tree for manifests, lockfiles, TSX, Vite/TSConfig, frontend directories, handoffs, decision/API matrices, and workflows
- official EUI package/provider/license material used for the WP-02 compatibility decision

Not present at the baseline: root `AGENTS.md`, `frontend/`, root or frontend
`package.json`, npm/pnpm/yarn lockfiles, Vite/TSConfig files, TSX application
files, `handoffs/`, `docs/react-migration/`, prior decision/API/route/verification
files, and `.github/workflows/`.

The only existing migration-control files were
`progress/MIGRATION_MASTER_PLAN.md` and `progress/WORK_PROGRESS.md`.

## Existing frontend result

No React/EUI/Vite scaffold exists in the inspected `main` tree. Uploaded
Phase-01 artifacts from a prior attempt also report that their expected local
workspace and deliverables were missing; they are failure evidence, not an
existing implementation. WP-02 therefore starts a new isolated `frontend/`
foundation rather than overwriting prior code.

## Scale snapshot

The source migration audit dated 2026-08-19 reports approximately:

| Area | Reported scale |
|---|---:|
| Repository files | 2,021 |
| PHP files | 1,657 |
| Browser JavaScript files | 167 |
| CSS files | 45 |
| PHP pages/partials/help/settings surfaces | about 277 |
| Legacy non-v1 browser PHP endpoints | about 719 |
| Public `/api/v1` routes | about 203 |
| Product modules | 22 plus separate portal/extension surfaces |

These are retained as the latest full static-audit counts. The current execution
environment could inspect the remote recursive tree but could not download it to
a local filesystem for an independent extension recount, so the numbers are not
misrepresented as a new local count.

## Current PHP UI and API boundaries

1. `index.php` and module PHP pages own the current session-backed, server-rendered UI.
2. `config.php` computes `BASE_URL` for root and subdirectory deployments.
3. `.htaccess` preserves pretty login/reset/CSAT URLs, `/a/<token>`, legacy redirects, and OAuth callback paths.
4. `/api/v1` is a public API-key API with a central route table and permission entries. It must not become the browser session API.
5. `api/<module>/*.php` is the large legacy browser/session API surface and remains unchanged in WP-01/WP-02.
6. `includes/services/` is the future business-logic reuse seam.
7. `includes/session_security.php` hardens authenticated sessions, while `includes/request_guard.php` documents that a complete CSRF-token system does not yet exist.

## WP-02 entry files

Create only the isolated frontend and review documents:

- `frontend/package.json`
- `frontend/index.html`
- `frontend/vite.config.ts`
- `frontend/tsconfig*.json`
- `frontend/src/main.tsx`
- `frontend/src/app/App.tsx`
- `frontend/src/app/providers/AppProviders.tsx`
- `frontend/src/app/router.tsx`
- `frontend/src/config/runtimeConfig.ts`
- `frontend/src/layouts/AppShell.tsx`
- `docs/react-migration/CODE_READING_GUIDE.md`
- `handoffs/WP-02.md`

Do not modify PHP, `.htaccess`, Docker production files, `/api/v1`, legacy APIs,
or any business module in WP-02.

## Risks and blockers

- No mounted target worktree: local uncommitted state cannot be observed.
- npm registry DNS is unavailable in the execution environment.
- EUI is not identified as MIT; resolved package notices require owner review.
- BrowserRouter fallback and runtime-config delivery need G1 approval.
- Session, CSRF, tenant, RBAC, and object scope are later contracts and must not be invented now.
- Existing callback, download, stream, cron, CSAT, and short-link routes must remain outside future SPA fallback.

## Actions confirmation

No GitHub Actions workflow was created, modified, executed, or used for evidence.
