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

## Current React client routes (post-G1)

The accepted deployment prefix is `${BASE_URL}ui/`. The original WP-02 proposal
used `${BASE_URL}app/`; that value is historical and was superseded by D-010.

| React route below basename | Purpose | Server/BFF dependency |
|---|---|---|
| `/` | Empty foundation review page | None |
| `/architecture` | Provider/build/runtime decisions for G1 | None |
| `/forbidden` | Reachable 403 UI skeleton | None |
| `/error` | Generic error UI skeleton | None |
| `*` | Explicit 404 skeleton | None |

`BrowserRouter` receives the normalized runtime basename, for example
`/freeitsm-app/ui`. Vite assets are relative and the document installs an early
runtime `<base>`.

## Deliberately not enabled

No `.htaccess`, IIS, nginx, PHP host, root redirect, or module route has been
changed for this React foundation. Production Apache deep-link fallback for
`/ui/*` is therefore **not implemented or verified**. The successful local
`preview:test` probe proves that the built artifact serves `/ui/assets/*` with
the correct MIME type and falls back on deep links; it is not evidence that
Apache refresh/deep-link routing works. Any future Apache
fallback must match only the selected `/ui/*` prefix and must exclude
API/auth/callback/setup/cron/download/static/CSAT/QR/stream paths.

This route work uses no GitHub Actions and creates no pull request. Go/go-zero
and clustered deployment remain future-only and are outside the current scope.
