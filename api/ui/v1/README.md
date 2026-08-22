# FreeITSM browser UI API v1

This directory is the isolated, same-origin contract surface for the React UI.
It is **not** the existing `/api/v1` machine API.

## WP-04 routes

```text
GET|HEAD|OPTIONS /api/ui/v1/
GET|HEAD|OPTIONS /api/ui/v1/health
```

`openapi.json` is the machine-readable contract source. Runtime requests are
handled only by `index.php` and the declarative `lib/routes.php` table.

WP-04 establishes transport mechanics only: versioned success/error envelopes,
strict path/method/JSON parsing, request/correlation IDs, same-origin response
headers, and unresolved actor/tenant/capability/locale/timezone context slots.
It does not start a Session, issue/validate CSRF, connect to the database,
authorize an actor, or expose a business endpoint. Those controls belong to
WP-05 and later work packages.

The root `.htaccess`, legacy PHP UI and `/api/v1` Bearer API-key routes are not
changed. The local `.htaccess` rewrites only this directory and emits no CORS
grant.

## Local verification

```bash
find api/ui/v1 -name '*.php' -print0 | xargs -0 -n1 php -l
php api/ui/v1/tests/run.php
node frontend/scripts/generate-ui-contract.mjs --check
```

Generated TypeScript is a transport DTO layer only. React feature/domain models
must remain handwritten adapters over the generated contract.
