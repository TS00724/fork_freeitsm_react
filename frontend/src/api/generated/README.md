# Generated UI API transport contracts

`ui-contract.ts` is generated from `api/ui/v1/openapi.json` by:

```bash
npm run generate:ui-contract
npm run verify:ui-contract
```

Do not hand-edit generated files. These types describe the HTTP transport only.
React features must map them into handwritten domain, form and view models; they
must not make a server payload shape the application's state model.

WP-04 does not add a Session/bootstrap client or any business request. Future
endpoint modules must continue using same-origin credentials rather than the
`/api/v1` machine API key.
