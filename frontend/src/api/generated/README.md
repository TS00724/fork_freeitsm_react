# Generated UI API transport types

`ui-contract.ts` is generated deterministically from
`api/ui/v1/openapi.json` by `frontend/scripts/generate-ui-contract.mjs`.

Generated DTOs and enums describe the HTTP transport only. React feature/domain
models remain handwritten and must map from these types instead of treating the
server payload as UI state.
