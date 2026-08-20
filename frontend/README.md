# FreeITSM React/EUI foundation (WP-02)

This directory is an isolated React 18 + strict TypeScript + Elastic UI review
shell. It does not replace, embed into, or modify the existing PHP UI.

## Intended local commands

```bash
cd frontend
npm ci
npm run verify
npm run dev
```

The package registry was unavailable in the implementation environment, so a
trustworthy lockfile and dependency-backed commands could not be produced or
executed. See `DEPENDENCY_INSTALL_BLOCKER.md` and the repository verification
report. Do not substitute a hand-written lockfile.

## Runtime paths

The default SPA prefix is `${BASE_URL}app/`. Public runtime settings are read
from `window.__FREEITSM_RUNTIME_CONFIG__`. The object must contain no secrets.
Vite build output is isolated at `frontend/dist/`.

The Vite development server may proxy existing `/api` and `/auth` requests to
`VITE_DEV_PHP_ORIGIN`. No UI BFF route is implemented and the current shell makes
no business API request.

## Scope boundary

- PHP remains independently runnable.
- No `.php` file belongs in `frontend/`.
- No business module exists under `src/features/` yet.
- No API response contract is assumed.
- No web-server fallback or production asset-copy step is enabled before G1.
- No GitHub Actions workflow is used.
