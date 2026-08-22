# FreeITSM route map

Repository: `TS00724/fork_freeitsm_react`

## Preserved legacy surfaces

WP-04 does not change the root `.htaccess`, legacy PHP module routes, OAuth
callbacks, CSAT/QR/download/feed/cron/webhook/stream routes, or the existing
`/api/v1` machine API. Existing PHP UI remains independently runnable.

## React surface

```text
${BASE_URL}ui/
```

React continues to use a runtime basename. Production Apache `/ui/*` history
fallback remains a separately reviewed server-integration task; WP-04 does not
claim it is implemented.

G1 performance rule remains mandatory: future business routes use lazy/dynamic
imports and heavyweight components are feature/component chunks. The recorded
foundation risk remains 1,641.07 kB minified / 510.78 kB gzip until measured and
reduced; WP-04 does not hide or solve it.

## Browser UI API surface

```text
/api/ui/v1/
├── index.php          single executable front controller
├── openapi.json       static contract source
├── lib/routes.php     declarative route table
└── health             process-only route
```

| External route | Methods | Resolution |
|---|---|---|
| `/api/ui/v1/` | `GET`, `HEAD`, `OPTIONS` | Foundation handler |
| `/api/ui/v1/health` | `GET`, `HEAD`, `OPTIONS` | Process health handler |
| `/api/ui/v1/openapi.json` | static `GET` by Apache/file server | OpenAPI 3.1 contract |
| any other `/api/ui/v1/*` | — | Front controller returns JSON 404 |
| known route with unsupported method | — | JSON 405 + `Allow` |

Only `api/ui/v1/.htaccess` is added. It rewrites non-file/non-directory requests
to `index.php`; it does not alter root routing and does not grant CORS.

## Machine API remains separate

```text
/api/v1/*
```

The existing machine surface continues using its Bearer API-key bootstrap and
route table. The React browser must not call it with a long-lived API key.

## Reserved WP-05 routes and behavior

The following are contract/security planning items only and **do not exist yet**:

```text
Session/bootstrap
login/logout integration
CSRF issuance/rotation/validation
tenant/company resolution or switch
capability/RBAC/object-scope enforcement
session-expiry browser behavior
```

No Calendar, Watchtower, Tickets, Assets, Knowledge, CMDB or other feature route
is registered by WP-04.
