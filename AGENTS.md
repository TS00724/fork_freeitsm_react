# AGENTS.md

This repository uses the versioned `universal-code-writing` skill as the default implementation and review guide.

## Required guide and audit sources

Before changing code, read:

1. `skills/universal-code-writing/SKILL.md`
2. `skills/universal-code-writing/references/language-profiles.md`
3. `skills/universal-code-writing/references/review-checklist.md`
4. `progress/WORK_PROGRESS.md`
5. the latest relevant `handoffs/WP-XX.md`
6. the active adjustment/work-package task named by `progress/WORK_PROGRESS.md`

The skill is the implementation guide. The review checklist is the minimum audit checklist for substantial patches. Repository-local architecture decisions and direct user instructions take precedence if they are more specific.

## Repository workflow

- Make the smallest coherent patch that satisfies the active work package/task.
- Do not combine a work package with unrelated cleanup or future features.
- Prefer focused sibling modules over growing central orchestration files.
- Split by domain responsibility, invariant, or testable boundary; never split mechanically into `part1`, `part2`, or meaningless one-use helpers.
- Preserve generated-code boundaries: update OpenAPI/schema/generator inputs and regenerate outputs; do not hand-edit generated transport files.
- Keep PHP legacy UI and React/EUI isolated according to the migration ADRs.
- Do not start a later WP unless `WORK_PROGRESS.md` and the user explicitly authorize it.

## Source-size and responsibility policy

These limits apply to new or materially changed React/UI-BFF code. Generated code and untouched legacy PHP are excluded.

| File type | Review threshold | Hard target | Required response |
|---|---:|---:|---|
| React `.tsx` page/component | 200 LOC | 300 LOC | split component/hook/model by responsibility |
| TypeScript `.ts` | 250 LOC | 400 LOC | split domain/adapter/validator/orchestration |
| Router/Provider/AppShell orchestration | 150 LOC | 250 LOC | move focused responsibilities to sibling modules |
| Node build/test `.mjs` | 250 LOC | 400 LOC | split parser/analyzer/report concerns |
| new `/api/ui/v1` PHP | 250 LOC | 400 LOC | split request/router/handler/context/contracts |
| test file | 300 LOC | 500 LOC | split by behavior/route/surface |

A file can require splitting below the numeric threshold when it mixes three or more distinct responsibilities, such as transport + mapping + state + rendering.

Exceptions require a written reason in the task/handoff. Generated files should be controlled by their source generator rather than manually split.

## Frontend bundle policy

G1 requires route/feature/component code splitting:

- business routes default to dynamic import / `React.lazy()` or an equivalent lazy mechanism;
- AppShell synchronously loads only startup-critical platform code;
- heavy DataGrid/chart/editor/CodeBlock-Prism/mapper/diagramming dependencies load at feature/component boundaries where practical;
- do not treat `manualChunks` alone as a performance fix if the initial route still downloads the same bytes;
- measure the actual `/ui/` initial-route gzip dependency closure, not only the largest output filename.

Historical G1 baseline: **1,641.07 kB minified / 510.78 kB gzip** for the single main chunk. It is technical debt, not an accepted future baseline.

## Project commands

Frontend (`frontend/`):

- clean install: `npm ci`
- full local verification: `npm run verify`
- typecheck: `npm run typecheck`
- lint: `npm run lint`
- unit/component tests: `npm run test`
- production build: `npm run build`
- browser E2E: `npm run test:e2e`
- accessibility: `npm run test:a11y`

PHP/UI-BFF:

- run `php -l` on every new or changed PHP file;
- run the focused contract/security-negative tests for the touched BFF boundary;
- never describe an unavailable runtime test as passed.

## Security and migration constraints

- Browser UI API: same-origin PHP Session direction; React must not read/store the PHP session id.
- Existing `/api/v1` remains the Bearer API-key machine surface; never expose those keys to React.
- Server authorization is authoritative; UI visibility is not a security boundary.
- No wildcard CORS with credentials.
- Preserve tenant/capability/object-scope fail-closed behavior.
- Do not introduce secrets, credentials, private endpoints, or sensitive logs.
- Go/go-zero, clustering and SOC runtime implementation remain future-only unless a later work package explicitly authorizes them.

## Repository safety

- Operate only on `TS00724/fork_freeitsm_react`.
- Do not add/use an upstream remote or write to the source repository.
- No force push.
- No Pull Request unless a later explicit user instruction changes this rule.
- Do not create, edit, run or depend on GitHub Actions.
- Before moving `main`, re-read `origin/main`, compare expected base/head, and use only a non-force fast-forward.

## Final audit

Before finalizing a substantial patch, audit it against `skills/universal-code-writing/references/review-checklist.md` and report:

- changed files and why;
- source-size/responsibility exceptions, if any;
- validation commands and exit codes;
- validation not run and exact blocker;
- compatibility/security/deployment risks;
- confirmation that the active work-package boundary was not exceeded.
