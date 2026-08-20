# WP-02 architecture snapshot

```text
Browser
  |
  |  /<BASE_URL>/app/*
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

                         G1 STOP
                            |
                            v (only after written approval)
Future PHP UI BFF /api/ui/v1  [NOT IMPLEMENTED]
                            |
Legacy PHP services/MySQL      [UNCHANGED]
```

Build output is `frontend/dist/`; no PHP path is a build target. The existing PHP root, session security, callbacks, public `/api/v1`, legacy APIs and module pages remain untouched.
