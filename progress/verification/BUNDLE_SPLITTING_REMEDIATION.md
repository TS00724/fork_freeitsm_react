# React startup bundle remediation

Date: 2026-08-23  
Repository: `TS00724/fork_freeitsm_react`  
Baseline `main`: `c1dd99792c1e31eae55341126c3dd0fde3f0b4cb`  
Working branch: `perf-main-chunk-lazy-split`

## Baseline problem

The recorded G1/WP-04 production build reports a single main JavaScript chunk of:

- 1,641.07 kB minified;
- 510.78 kB gzip.

That value remains the historical baseline. This remediation must not hide the
number by merely renaming or manually repartitioning an equally large synchronous
startup payload.

## Source changes

1. `frontend/src/app/router.tsx`
   - foundation route modules now use `React.lazy()` / dynamic `import()`;
   - Home, Architecture, 403, Error and 404 pages are separate dynamic entries;
   - the AppShell remains the synchronous startup boundary.
2. `frontend/src/pages/HomePage.tsx`
   - removes `EuiCodeBlock` from the default route;
   - uses lightweight semantic `<pre><code>` output instead, so Prism/Refractor is
     not required by the default foundation page.
3. `frontend/vite.config.ts`
   - emits Vite's build manifest for measurable chunk accounting.
4. `frontend/scripts/verify-bundle-budget.mjs`
   - dependency-free gzip accounting using Node `zlib`;
   - calculates the synchronous entry/static dependency closure;
   - calculates the actual default `/ui/` route transfer by adding HomePage's
     dynamic/static closure without double-counting shared chunks;
   - requires HomePage to remain a dynamic entry;
   - requires at least five dynamic route chunks;
   - requires entry gzip <= 500,000 bytes;
   - requires real default-route JS gzip < the old 510,780-byte baseline.
5. `frontend/package.json`
   - adds `npm run verify:bundle` and includes it after the production build in
     the standard `npm run verify` gate.
6. `frontend/tests/App.test.tsx`
   - route assertions now await lazy modules.

## Why this is not a manualChunks-only change

Manual vendor chunks can make the file named `main` smaller while leaving the
same total startup bytes. The bundle gate therefore sums the manifest's real
static dependency closure plus the immediately requested HomePage closure. A
split with no real `/ui/` transfer reduction fails.

## Verification actually performed in this execution environment

The command runtime cannot resolve `github.com`/npm, so a fresh repository clone,
`npm ci`, and the real Vite production build could not be executed here. The
510.78 kB baseline is therefore **not yet replaced by a new measured number**.

Dependency-free checks performed against the authored changes:

- `node --check` on the bundle-budget script: exit 0;
- synthetic Vite-manifest test of entry/static/HomePage closure accounting: exit 0;
- TypeScript 5.8.3 syntax/type-shape smoke compile of the changed router/HomePage
  using minimal module stubs: exit 0.

These checks prove the authored verification logic is executable; they are not a
substitute for the repository's real `npm run verify`.

## Required acceptance commands

Run on a Codex/local environment that has the repository checkout and npm access:

```bash
cd frontend
npm ci
npm run verify
```

`npm run verify` must print, and retain as evidence:

```text
Bundle entry: ...
Entry gzip bytes: ...
Synchronous shell transfer gzip bytes: ...
Default /ui/ route transfer gzip bytes: ...
Previous G1 single-main gzip bytes: 510780
Default-route improvement bytes: ...
Dynamic imports from entry: ...
```

Acceptance requires:

- full typecheck/lint/unit/coverage/build/preview gate exit 0;
- `verify:bundle` exit 0;
- default `/ui/` JS transfer < 510,780 gzip bytes;
- entry gzip <= 500,000 bytes;
- at least five dynamic route entries;
- no regression in direct `/ui/forbidden` or 404 routing.

If the default route remains >= 510,780 gzip bytes, this remediation is **not
complete**; inspect the manifest/chunk list next, especially startup EUI/theme
imports, and continue splitting based on measured contributors rather than
changing the threshold.

## Scope safety

This branch does not start WP-05 and does not modify PHP, `/api/ui/v1`, root
`.htaccess`, business features, Go/go-zero, GitHub Actions or pull requests.
