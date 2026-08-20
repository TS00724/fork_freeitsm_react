# FreeITSM API boundary matrix — WP-01

This matrix records migration boundaries only. It does not define or implement a
UI-BFF contract.

| Surface | Current auth/transport | Current contract | React use in WP-02 | Future disposition | Status/risk |
|---|---|---|---|---|---|
| `/api/v1` | Bearer API key; external machine API | Central controller/routes, HTTP statuses, permissions, OpenAPI-style resources | **Never called** | Preserve compatibility; reuse services internally where appropriate | API keys must never enter browser code |
| `api/<module>/*.php` | Mostly PHP Session; mixed form/JSON | Per-file inputs and mixed `{success}` responses/statuses | **Never called** | Inventory and migrate incrementally after G1/G2 | Large CSRF/tenant/RBAC consistency surface |
| `api/external/*` | Device/agent integration contracts | External submit/query endpoints | **Never called** | Preserve and test separately | Deployed agents may depend on paths/fields |
| OAuth/OIDC/Google callbacks | Redirect/callback URLs registered externally | Exact URL contract | **Never changed** | Preserve; later SPA may initiate server redirect only | URL changes can break provider registrations |
| Login/logout/MFA/password PHP | Session, redirects, forms | Existing analyst/portal flows | Placeholder context only | Design UI-session contract after G1 | Do not implement auth in WP-02 |
| CSAT and `/a/<token>` | Public token links | Sent-email/printed-label compatibility | **Never changed** | Keep outside SPA fallback | Long-lived external links |
| Cron/webhooks/iCal/feed | Server/third-party specific | Non-browser and feed semantics | **Never called** | Preserve specialized routes | Not ordinary JSON CRUD |
| File upload/download | Session/object checks; multipart/binary | Endpoint-specific | Transport supports `FormData`/raw `Response` only | Define explicit file contract later | Must test authorization, size, content type |
| SSE/streaming AI | Streaming response | Endpoint-specific stream semantics | Not implemented | Add reviewed stream adapter later | Do not buffer into ordinary JSON |
| Future `/api/ui/v1` | Proposed same-origin Session + CSRF | **Unknown / not implemented** | Base URL and transport extension point only | WP-04/WP-05 after G1 | No response fields may be assumed now |

## Public v1 resource families observed

The route table covers tickets, assets, problems, changes, knowledge, tasks,
CMDB, contracts/suppliers, calendar, software, service status, morning checks,
forms, workflows, network diagrams, users/analysts, companies, and reference
lookups. WP-01/WP-02 do not alter the table.

## Unknown contract register

The following remain explicitly undecided until later work packages:

- bootstrap/session DTO fields;
- CSRF issue/rotation/header rules;
- tenant-context selection and object-scope representation;
- capability naming and module-access model;
- error envelope and stable codes for the UI API;
- locale/theme/branding/preferences payloads;
- upload/download and stream route conventions;
- OpenAPI/type-generation toolchain.

No endpoint in this matrix is marked migrated or verified by WP-01/WP-02.
