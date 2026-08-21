# ADR-002 — future UI API authentication and CSRF boundary

Date: 2026-08-21  
Status: Direction accepted at G1; field-level contract deferred to BFF work

## Existing source constraints

- `api/v1` is a Bearer API-key machine API and must remain separate from browser authentication.
- `includes/session_security.php` already hardens authenticated PHP sessions and rotates IDs.
- `includes/request_guard.php` explicitly documents that the application does not yet have a complete CSRF-token mechanism.

## Future browser-auth decision

1. Continue using the server-side PHP Session as the browser identity mechanism during the PHP-backend phase.
2. React must not read or store the PHP session identifier. Requests use `credentials: 'same-origin'` and the browser sends the HttpOnly cookie.
3. The future UI API lives under `/api/ui/v1`; it must not reuse a long-lived `/api/v1` key in the browser.
4. HTTP semantics:
   - `401` = no valid authenticated session / session expired.
   - `403` = authenticated but the user lacks capability, tenant scope or object scope.
5. State-changing UI requests will use a **session-bound synchronizer CSRF token**. The server issues a token through an approved bootstrap/session response; the client sends it in a dedicated header for POST/PATCH/PUT/DELETE.
6. CSRF validation is layered with SameSite cookie protection plus same-origin `Origin` validation (and Referer fallback where appropriate). No wildcard CORS with credentials.
7. Server authorization remains authoritative. React permission state is only presentation guidance.

## Intentionally deferred

The following are not frozen in WP-03:

- exact bootstrap/session JSON field names;
- exact CSRF header name and rotation timing;
- login/MFA/password endpoint schemas;
- tenant/capability DTO vocabulary;
- stable UI error-envelope field names;
- SOC identity claim names.

Those contracts belong to the later BFF/security packages and must be defined from the PHP source behavior and negative tests before implementation.
