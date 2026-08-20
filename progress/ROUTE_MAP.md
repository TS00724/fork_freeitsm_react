# FreeITSM route map — WP-01/WP-02

## Existing routes that must remain authoritative

| Existing route | Current target/meaning | WP-02 action |
|---|---|---|
| `/` and module PHP paths | Legacy PHP application and module landing pages | Unchanged |
| `/login`, `/logout`, `/forgot-password`, `/reset-password` | Pretty auth URLs rewritten to PHP | Unchanged |
| `/csat` and legacy `csat.php?...` | Long-lived survey links | Unchanged |
| `/a/<token>` | Asset QR short link | Unchanged |
| `/oauth_callback.php`, `/google_oauth_callback.php` | Externally registered callbacks | Unchanged |
| `/api/v1/*` | Public machine API | Unchanged |
| `/api/<module>/*.php` | Legacy browser/session endpoints | Unchanged |
| cron, webhook, feed, file, and stream routes | Specialized server/external transports | Unchanged |

## WP-02 client routes

Proposed deployment prefix: `${BASE_URL}app/`.

| React route below basename | Purpose | Server/BFF dependency |
|---|---|---|
| `/` | Empty foundation review page | None |
| `/architecture` | Provider/build/runtime decisions for G1 | None |
| `/forbidden` | Reachable 403 UI skeleton | None |
| `/error` | Generic error UI skeleton | None |
| `*` | Explicit 404 skeleton | None |

`BrowserRouter` receives the normalized runtime basename, for example
`/freeitsm-app/app`. Vite assets are relative and the document installs an early
runtime `<base>`.

## Deliberately not enabled

No `.htaccess`, IIS, nginx, PHP host, root redirect, or module route is changed in
WP-02. Therefore production deep-link fallback is a G1 deployment decision, not
a completed server feature. Any future fallback must match only the selected
`/app/*` prefix and must exclude API/auth/callback/setup/cron/download/static/
CSAT/QR/stream paths.
