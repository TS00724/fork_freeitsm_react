# ADR-004 — UI API Session, CSRF and authorization context

Date: 2026-08-27  
Status: Implemented on WP-05 branch; G2/runtime evidence pending

## Context

WP-04 created a transport-only `/api/ui/v1` front controller. It deliberately
left actor, tenant, capabilities, locale and timezone unresolved. The existing
FreeITSM analyst login already supports local passwords, LDAP, OIDC, MFA,
Session ID rotation and hardened cookies; the browser BFF must consume that
identity rather than introducing a second login or exposing `/api/v1` keys.

## Decision

1. Legacy analyst authentication remains authoritative. React navigates to the
   existing login/logout paths and never posts credentials to the UI BFF.
2. `GET /api/ui/v1/session` is an optional-Session probe:
   - anonymous: 200 with `authenticated:false` and login links;
   - authenticated: actor, active/available tenant, modules, capabilities,
     locale, timezone, CSRF token and navigation links;
   - invalid/stale actor: 401 and stale authentication keys are cleared;
   - password-change gate: 403.
3. The PHP Session cookie remains HttpOnly and browser-managed. The UI BFF sets
   strict mode, cookies-only mode, SameSite Lax-or-stronger and Secure on HTTPS
   before starting its Session. It never returns the Session identifier.
4. Unsafe BFF routes use `X-CSRF-Token`, a 256-bit synchronizer token stored in
   the Session and bound to a hash of the current Session ID. Session ID changes
   invalidate the token; tenant switching rotates it explicitly.
5. CSRF also requires exact same-origin `Origin`, or exact same-origin `Referer`
   fallback. Missing, foreign, null, user-info-bearing or malformed origins fail
   closed. `TRUST_PROXY_HTTPS` is honored only through the existing trusted-proxy
   configuration.
6. CSRF is checked after a Session actor ID is present but before DB-backed
   actor, tenant or RBAC resolution.
7. Active tenant is selected only from active tenants the analyst can access.
   Single-company installs always resolve to Default without requiring explicit
   multi-tenant grant rows.
8. Actor administration, modules and capabilities are resolved from the
   database on each request. Session/UI data is presentation guidance only.
9. `UiApiRouteSecurity` is the single route-policy vocabulary for required
   Session, CSRF, module, capability, tenant parameter and object scope.
10. Object-scope denial defaults to 404 so object existence is not disclosed.
11. `/api/v1` remains the independent Bearer machine API. No wildcard CORS,
    browser API key or direct React database access is introduced.

## Contract

The authoritative WP-05+ contract is `api/ui/v1/openapi-v1.json`; generated
transport types are `frontend/src/api/generated/ui-v1-contract.ts`. The WP-04
`openapi.json`/`ui-contract.ts` pair remains a compatibility snapshot and is not
silently rewritten.

## Consequences

- Future business routes must declare server-side policies before handlers run.
- Tenant/capability data can drive React visibility but cannot authorize a write.
- Login/MFA/OIDC/LDAP behavior stays in one legacy implementation during the PHP
  backend phase.
- A real MySQL fixture is still needed to prove current production schema,
  permissions and pre-upgrade behavior end to end.
- ADJ-001 Phase B and Playwright/axe remain separately deferred; neither is
  claimed as Passed by this ADR.

## Rejected alternatives

- JWT or Session IDs in localStorage;
- React reading `PHPSESSID`;
- reusing a long-lived `/api/v1` key in the browser;
- trusting frontend capability checks;
- checking only SameSite without a synchronizer token;
- wildcard CORS with credentials;
- tenant selection from an unvalidated client-supplied ID.
