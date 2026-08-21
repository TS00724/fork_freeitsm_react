# React/EUI architecture snapshot after G1

```text
Browser
  |
  |  /<BASE_URL>/ui/*
  v
frontend/ React 18 + TypeScript + EUI
  |-- RuntimeConfigProvider ---- public base/locale/timezone only
  |-- EUI ThemeProvider -------- light/dark + Borealis
  |-- Auth boundary ------------ unresolved placeholder
  |-- Tenant boundary ---------- unresolved placeholder
  |-- Permission boundary ------ unresolved placeholder
  |-- BrowserRouter ------------ runtime basename
  |-- AppShell ----------------- architecture-review shell only
  `-- API transport placeholder - no endpoint contracts, no calls

                 G1 ARCHITECTURE APPROVED
                            |
                            v (WP-04/05 only after verification closure)
Future PHP UI BFF /api/ui/v1  [NOT IMPLEMENTED]
                            |
Legacy PHP services/MySQL      [UNCHANGED]
```

Build output is `frontend/dist/`; no PHP path is a build target. The existing PHP root, session security, callbacks, public `/api/v1`, legacy APIs and module pages remain untouched.

The historical WP-02 proposal used `/app/*`; D-010 superseded it with `/ui/*`.
A real `package-lock.json` now exists, `npm ci` has succeeded, and the complete
non-browser `npm run verify` gate passes, including typecheck, lint, 43 tests,
coverage thresholds, production build, and the `/ui/` static preview probe.
Exact results and the earlier corrected failures are recorded in
`progress/VERIFICATION_REPORT.md`.

Production Apache fallback for `/ui/*` is not implemented or verified. Vite
development and `npm run preview:test` are frontend-only test servers and are
not evidence of Apache rewrite behavior. Playwright/axe remains unexecuted at
browser level because this environment could not download browser binaries.
No GitHub Actions workflow or pull request is used. Go/go-zero, clustering, and
the future SOC host implementation remain outside the current execution scope.
