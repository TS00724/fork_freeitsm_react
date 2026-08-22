# WP-03 G1 runtime verification attempt — 2026-08-22

## Scope and immutable baseline

- Repository: `TS00724/fork_freeitsm_react`
- Required starting `main`: `132d77e2e7ea88910bee3fa45819ac7e18c635ec`
- Evidence branch: `g1-closure-runtime-evidence`
- Branch base: `132d77e2e7ea88910bee3fa45819ac7e18c635ec`
- Scope: G1 remaining verification only
- Pull request: none
- GitHub Actions: not created, modified, run, or used
- Upstream remote/write: none
- PHP, `.htaccess`, `/api/ui/v1`, BFF, Calendar, Watchtower, Tickets, other features, Go/go-zero and SOC cluster work: not modified or started

Immediately before this evidence commit, remote `main` was re-read and still
pointed to the required SHA.

## Required source review

The following files were read from the exact required remote SHA before the
attempt:

1. `progress/G1_CLOSURE_CHECKLIST.md`
2. `progress/VERIFICATION_REPORT.md`
3. `progress/WORK_PROGRESS.md`
4. `handoffs/WP-03.md`
5. `frontend/playwright.config.ts`
6. `frontend/e2e/g1-shell.spec.ts`
7. `frontend/e2e/a11y.spec.ts`
8. `docs/react-migration/THIRD_PARTY_REVIEW.md`

The existing successful non-browser evidence remains attached to source commit
`46c901597557abe7f319a880c9a3539105307196`. This attempt does not rewrite it.
The known production bundle risk also remains open: the main chunk is
**1,641.07 kB minified / 510.78 kB gzip**.

## Runtime environment

```text
Node: v22.16.0
npm:  10.9.2
Git:  2.47.3
System Chromium: 144.0.7559.20
Preinstalled supplemental Python Playwright: 1.57.0
Project Playwright requested by lockfile: 1.62.1
```

The container had no working DNS/network route to `github.com`,
`registry.npmjs.org`, `cdn.playwright.dev`, or
`playwright.download.prss.microsoft.com`.

## Repository checkout attempt

```bash
git clone --no-tags --single-branch --branch main \
  https://github.com/TS00724/fork_freeitsm_react.git \
  /mnt/data/g1_exact_checkout
```

```text
exit code: 128
fatal: unable to access 'https://github.com/TS00724/fork_freeitsm_react.git/':
Could not resolve host: github.com
```

Therefore this runtime could not materialize the exact checkout. Literal local
`git status --short`, `git branch --show-current`, `git rev-parse HEAD`,
`git remote -v`, and clean-worktree assertions were not fabricated. Repository,
clone URL, branch and SHA were instead verified read-only through the GitHub
connector.

## Required npm command attempts

The exact remote `frontend/package.json` and lockfile-v3 were inspected through
the repository connector, but the connector does not mount files into this
runtime. A manifest-only diagnostic directory was used solely to capture the
runtime failure modes below. These results are **not repository test results**
and are not treated as source-code failures.

| Command | Exit | Actual result |
|---|---:|---|
| `npm ci --ignore-scripts --fetch-retries=0 --fetch-timeout=5000` | 1 | Exact repository lockfile could not be materialized in the diagnostic directory; npm rejected the install because no usable lockfile was available locally |
| `npm run verify` | 1 | Exact source tree was unavailable; `scripts/verify-structure.mjs` could not be found |
| `npm run test` | 127 | Dependencies were not installed; `vitest: not found` |
| `npm run test:e2e` | 1 | Project `@playwright/test` was unavailable; PATH resolved the unrelated Python Playwright CLI, which has no `test` subcommand |
| `npm run test:a11y` | 1 | Same missing project Playwright boundary; no axe assertion ran |

These diagnostics do not replace the previously recorded successful clean
`npm ci` and non-browser `npm run verify` for the reviewed source commit. They
also do not satisfy the requested current-runtime rerun.

## Playwright browser installation attempts

### Pinned project version

```bash
npx --yes playwright@1.62.1 install chromium firefox webkit
```

```text
exit code: 1
npm error code EAI_AGAIN
npm error syscall getaddrinfo
npm error request to https://registry.npmjs.org/playwright failed
npm error reason: getaddrinfo EAI_AGAIN registry.npmjs.org
```

### Supplemental preinstalled Playwright version

```bash
/opt/pyvenv/bin/playwright install chromium firefox webkit
```

```text
exit code: 1
Error: getaddrinfo EAI_AGAIN cdn.playwright.dev
Error: getaddrinfo EAI_AGAIN playwright.download.prss.microsoft.com
Failed to install browsers
```

No project Chromium, Firefox or WebKit browser assertion executed. No axe
serious/critical assertion executed. Browser automation is **Blocked**, not
Passed.

A supplemental synthetic launch with the system Chromium binary and the
preinstalled Python Playwright returned exit 0 and rendered a local in-memory
HTML page. It proves only that the system Chromium executable can start. It is
not the pinned Playwright 1.62.1 browser matrix, did not load FreeITSM, and is not
accepted as G1 evidence.

## G1 closure state after this attempt

| Required closure item | Result |
|---|---|
| Pinned Chromium / Firefox / WebKit Playwright tests | **Blocked — browser/package downloads unavailable** |
| axe serious/critical scan | **Not run** |
| User Human QoS | **Pending user: `Pass`, `Needs tuning`, or `Block`** |
| EUI/Elastic terms | **Pending owner/legal: `Accepted`, `Rejected`, or `Needs legal review`** |
| WP-02 / WP-03 Verified complete | **No** |
| WP-04 / WP-05 / BFF / features | **Not started** |

No progress percentage is raised by this attempt. Final G1 closure still
requires successful pinned browser execution plus the two explicit user/legal
records in `progress/G1_CLOSURE_CHECKLIST.md`.
