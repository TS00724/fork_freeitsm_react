# FreeITSM API matrix

Repository: `TS00724/fork_freeitsm_react`  
Current scope: WP-04 browser UI API contract foundation

## API surface separation

| Surface | Prefix | Intended caller | Authentication | Current status |
|---|---|---|---|---|
| Legacy/internal PHP endpoints | Existing module paths and legacy API files | Existing PHP UI | Existing PHP Session / endpoint-specific rules | Unchanged by WP-04 |
| Machine/integration API | `/api/v1` | Integrations and machines | Bearer API key | Existing and unchanged; must never be exposed to React |
| Browser UI API | `/api/ui/v1` | Same-origin React UI | PHP Session direction accepted at G1; authoritative implementation belongs to WP-05 | WP-04 transport/contract foundation implemented |

The browser surface emits no wildcard CORS grant, reads no Bearer API key, does
not load the machine API bootstrap, and does not expose PHP Session identifiers
to JavaScript.

## WP-04 executable routes

| Method | Route | Purpose | Auth/DB behavior |
|---|---|---|---|
| `GET`, `HEAD`, `OPTIONS` | `/api/ui/v1/` | Describe the browser BFF foundation and unresolved security slots | No Session, DB, tenant or capability lookup |
| `GET`, `HEAD`, `OPTIONS` | `/api/ui/v1/health` | PHP-process-only health | Reports database/session as `not_checked`; not a dependency health claim |

Static contract source:

```text
/api/ui/v1/openapi.json
```

No login/logout, bootstrap, CSRF, tenant switch, analyst, permission or business
route exists in WP-04.

## Envelope contract

Success:

```json
{
  "data": {},
  "meta": {
    "apiVersion": "1",
    "requestId": "...",
    "correlationId": "...",
    "timestamp": "UTC date-time"
  }
}
```

Error:

```json
{
  "error": {
    "code": "not_found",
    "message": "...",
    "details": {}
  },
  "meta": {
    "apiVersion": "1",
    "requestId": "...",
    "correlationId": "...",
    "timestamp": "UTC date-time"
  }
}
```

`details` is optional. Unexpected 500 responses never expose stack traces,
filesystem paths, exception messages or secrets.

## Status semantics

| Status | Contract meaning | Implementation boundary |
|---:|---|---|
| 400 | Invalid method token, unsafe path, malformed/non-object JSON, invalid route parameter | WP-04 |
| 401 | Missing/expired PHP Session | Defined now; authoritative WP-05 implementation pending |
| 403 | Authenticated but tenant/capability/object scope denied | Defined now; authoritative WP-05 implementation pending |
| 404 | Unknown endpoint or intentionally hidden object | Router implemented; object hiding later |
| 405 | Known route with unsupported method; `Allow` returned | WP-04 |
| 409 | Write/version/state conflict | Contract defined; business use later |
| 415 | Unsupported body media type | WP-04 |
| 422 | Semantic validation failure | Contract defined; business use later |
| 429 | Rate limited; optional `Retry-After` | Contract defined; shared limiter later |
| 500 | Generic unexpected server failure | WP-04 containment implemented |

## Request context slots

Every future handler receives a context with:

```text
request ID
correlation ID
actor               unresolved in WP-04
tenant/company       unresolved in WP-04
capabilities         unresolved in WP-04
locale               unresolved in WP-04
timezone             unresolved in WP-04
```

WP-05 must populate these from authoritative server-side state. React UI
visibility is not an authorization boundary.

## Contract generation

`api/ui/v1/openapi.json` is the source of transport DTOs/enums. The dependency-
free generator produces:

```text
frontend/src/api/generated/ui-contract.ts
```

Generated transport types must be mapped into handwritten frontend domain/view
models. No business response shape has been invented in WP-04.
