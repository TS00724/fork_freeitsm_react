# ADR-003: Browser UI API v1 contract foundation

- Date: 2026-08-22
- Status: Accepted for WP-04
- Scope: transport/contracts only

## Context

React must not use the existing `/api/v1` Bearer API-key surface. The browser
needs a same-origin, PHP-Session-oriented BFF whose transport rules are stable
before Session, CSRF, tenant and authorization code is introduced.

## Decision

1. The browser API namespace is `/api/ui/v1`.
2. One `index.php` front controller and one declarative route table own
   executable requests.
3. `/api/v1` remains an independent machine API and is not loaded or called with
   a browser-held API key.
4. Success and error responses use one versioned JSON envelope containing UTC
   timestamp, request ID and correlation ID.
5. Incoming IDs are validated; invalid values are replaced. Correlation ID
   defaults to the request ID.
6. The request boundary validates method token, normalized path, media type,
   JSON-object body and typed route parameters before a handler runs.
7. Unexpected failures return a generic 500 envelope; server logs contain only
   correlation metadata, method/path and exception class, never request body,
   cookies, authorization data, stack or secret values.
8. Context slots for actor, tenant/company, capabilities, locale and timezone
   exist but remain unresolved in WP-04.
9. OpenAPI 3.1 is the transport source. A dependency-free generator produces
   TypeScript DTOs/enums; features must map them into handwritten domain/view
   models.
10. Only foundation `/` and process-only `/health` routes exist in WP-04.

## Explicit non-decisions

WP-04 does not decide or implement the final Session/bootstrap payload, CSRF
issuance/rotation, tenant selection, RBAC/capability vocabulary, object-scope
rules, login/logout or session-expiry browser flow. Those are WP-05 review items.

It also does not change root Apache routing, implement React business routes, or
resolve the known 510.78 kB gzip frontend chunk. G1 lazy-loading rules remain in
force for later feature work.

## Consequences

- Transport behavior can be tested without a database or Session.
- WP-05 receives one narrow location to bind authoritative security state.
- Generated transport drift is checked by the normal frontend verification
  command.
- The API exposes no wildcard CORS and cannot silently inherit the machine API's
  Bearer authentication.
- Any future route that bypasses the front controller, common envelope or
  context boundary requires a superseding ADR and explicit review.
