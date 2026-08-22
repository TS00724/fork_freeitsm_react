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

## Security boundary

WP-04 establishes transport mechanics only:

- same-origin JSON; no wildcard CORS and no browser API key;
- versioned success/error envelopes;
- validated request and correlation IDs;
- strict path, method, Content-Type and JSON-object parsing;
- actor, tenant/company, capability, locale and timezone context slots;
- generic external 500 responses with server-side correlation logging.

It deliberately does **not** start a PHP Session, issue/validate CSRF tokens,
load an analyst, choose a tenant, authorize a capability/object, query the
database, or implement a business module. Those authoritative controls belong
to WP-05 and later packages.

The root `.htaccess`, legacy PHP UI and `/api/v1` Bearer API-key routes are not
changed by WP-04. The local `.htaccess` rewrites only this directory.

## Local verification

```bash
find api/ui/v1 -name '*.php' -print0 | xargs -0 -n1 php -l
php api/ui/v1/tests/run.php
node frontend/scripts/generate-ui-contract.mjs --check
```

The generated frontend file is a transport DTO layer only. React feature/domain
models must remain handwritten adapters over the generated contract.
