# FreeITSM React/EUI foundation

This directory is the isolated React 18 + strict TypeScript + Elastic UI migration workspace. The legacy PHP UI remains independently runnable and authoritative for every unmigrated module.

## Current G1 decisions

- React mount prefix: `${BASE_URL}ui/`
- current server target: Apache
- future deployment target may include Nginx/Go-zero, but neither replaces Apache in WP-03
- AppShell follows the legacy PHP information architecture while using EUI components/patterns
- PHP and React remain in parallel during strangler migration
- initial theme: Light
- minimum locale set: English, Simplified Chinese and Traditional Chinese
- timezone: independently configurable
- API typing: generated transport DTOs + handwritten frontend models
- first recommended future vertical slice: Calendar
- automated browser testing: Playwright; Selenium is fallback only

## Intended local commands

```bash
cd frontend
npm ci
npm run verify
npm run dev
```

For browser automation, after a reviewed lockfile and Playwright browser install:

```bash
npx playwright install
npm run test:e2e
npm run test:a11y
```

The current execution environment has not resolved the existing npm registry/lockfile blocker, so dependency-backed checks and the newly authored Playwright tests must not be reported as executed until that blocker is cleared.

## Runtime paths

The default SPA prefix is `${BASE_URL}ui/`. Public runtime settings are read from `window.__FREEITSM_RUNTIME_CONFIG__`; the object must contain no secrets. Vite build output is isolated at `frontend/dist/`.

The Vite development server may proxy existing `/api` and `/auth` requests to `VITE_DEV_PHP_ORIGIN`. This does not create a UI BFF. No `/api/ui/v1` server handler or business API call is implemented in WP-03.

## Scope boundary

- PHP remains independently runnable.
- No `.php` file belongs in `frontend/`.
- No business module exists under `src/features/` yet.
- No concrete UI API response contract is assumed.
- No production `/ui/*` Apache fallback is enabled yet.
- No GitHub Actions workflow is used.
- WP-03 records architecture decisions and test scaffolding only; BFF/session/CSRF/RBAC implementation is a later package.
