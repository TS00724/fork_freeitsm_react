# React migration test strategy

Date: 2026-08-21  
Primary browser automation: **Playwright**  
Selenium status: compatibility fallback only

## Why Playwright

Playwright is the primary E2E framework because one test API covers Chromium, Firefox and WebKit and provides direct support for traces, screenshots, videos, browser contexts, request interception and reliable deep-link navigation. Selenium may be used later when an organizational grid or a browser/device combination requires it, but the project should not maintain two equivalent E2E suites by default.

## Five-layer quality gate

### 1. Static

Required for every frontend change when dependencies are available:

- `npm run typecheck`
- `npm run lint`
- `npm run build`
- `git diff --check`

### 2. Unit/component and coverage

Initial targets:

- statements >= 80%
- lines >= 80%
- functions >= 80%
- branches >= 75%

Security/platform code such as API transport, auth/session state, tenant, permission, CSRF helpers and route guards should target >= 90% where meaningful. Coverage is a regression signal, not a target to game with low-value assertions.

### 3. Accessibility

Use `@axe-core/playwright` for automated checks and block `serious` or `critical` violations on gate routes. Automated checks are supplemented by keyboard-only navigation, focus-order review, accessible names, heading/landmark review and contrast review.

### 4. Playwright E2E and security-negative tests

Foundation coverage starts with:

- `/ui/` direct load;
- approved Light default;
- `/ui/forbidden` direct navigation;
- unknown-route 404;
- Chromium, Firefox and WebKit.

After the UI API exists, add:

- session-expired -> 401 behavior;
- authenticated/no-capability -> 403 behavior;
- CSRF rejection;
- cross-tenant object rejection;
- deep-link refresh/back navigation;
- locale and timezone rendering;
- write-flow retries/conflict/error states.

### 5. Human QoS acceptance

Important gates still require the user to exercise the product. Human QoS evaluates usability, responsiveness, visual consistency, navigation clarity, error clarity, perceived performance and legacy parity. Result values: `Pass`, `Needs tuning`, `Block`.

## Local commands

With the reviewed project lockfile and dependencies installed:

```bash
cd frontend
npm ci
npm run verify
npx playwright install
npm run test:e2e
npm run test:a11y
```

No GitHub Actions workflow is created or required. No pull request is required
for this local verification work. Test reports remain local artifacts unless a
later non-Actions reporting mechanism is explicitly approved.

## Current verification status

The historical WP-02 npm registry failure is retained in its evidence files. A
real `frontend/package-lock.json` has been generated and `npm ci` succeeds. The
final non-browser `npm run verify` command also succeeds: structure/isolation,
lockfile, strict typecheck, lint, 43 tests with coverage thresholds, production
build, and `verify:preview` all pass.

Playwright browser installation failed before browser execution because the
external download returned invalid/truncated archives and a proxy certificate
502. Consequently the 18 Chromium/Firefox/WebKit cases, including axe, are
**not passed or failed assertions**; they are blocked at browser launch. Human
QoS is also pending. The local `preview:test` harness proves built `/ui/` asset
delivery and SPA fallback only; it is not production Apache rewrite evidence.

Go/go-zero and clustered deployment are future architecture concerns, not test
targets in the current work package.
