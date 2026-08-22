# FreeITSM browser UI API v1 — WP-04 foundation

This directory is the browser-only, same-origin BFF contract boundary. It is not
the existing machine API under `/api/v1`.

## Implemented in WP-04

- one `index.php` front controller;
- a declarative and testable route table;
- consistent success/error JSON envelopes;
- request and correlation identifiers in headers and response metadata;
- strict path, JSON-object and Content-Type parsing;
- 404/405/OPTIONS behavior and safe 500 handling;
- request-context slots for future actor, tenant/company, capabilities, locale
  and timezone;
- checked-in OpenAPI 3.1 source and deterministic TypeScript transport types;
- local PHP contract tests.

## Deliberately not implemented until WP-05

- login/logout/session bootstrap behavior;
- CSRF token issue, rotation or validation;
- tenant/company selection or enforcement;
- capability/RBAC/object-scope authorization;
- MFA or session-expiry browser flows.

The foundation routes are only `GET /` and `GET /health`. No business module is
available here.

## URL forms

With Apache `mod_rewrite`:

```text
/api/ui/v1/
/api/ui/v1/health
```

Without rewrite support, PHP `PATH_INFO` remains usable:

```text
/api/ui/v1/index.php/
/api/ui/v1/index.php/health
```

No wildcard CORS header is emitted. Browser requests remain same-origin and the
machine `/api/v1` Bearer key must never be copied into React.

## Local verification

```bash
find api/ui/v1 -name '*.php' -print0 | xargs -0 -n1 php -l
php api/ui/v1/tests/run.php
bash api/ui/v1/tests/http-smoke.sh
```

The HTTP smoke script exercises the explicit `index.php/PATH_INFO` form using
PHP's built-in server. It does **not** claim that production Apache rewrite rules
have been tested; that remains a separate, narrow server-integration task.
