# ADJ-001 Phase B deferral and WP-05 authorization

Date: 2026-08-25  
Repository: `TS00724/fork_freeitsm_react`

## Owner decision

The project owner accepts ADJ-001 Phase A and authorizes WP-05 to start. The
runtime-dependent Phase B is deferred to a later execution environment.

This decision does **not** mark the following as Passed:

- clean `npm ci` on the ADJ-integrated tree;
- full frontend verification on that tree;
- actual `/ui/` initial-route gzip measurement;
- top-10 real JavaScript chunks;
- an evidence-derived forward budget;
- Chromium/Firefox/WebKit/axe.

## Feasibility confidence

Engineering confidence that the implemented structure can reduce the real
`/ui/` initial JavaScript transfer below the historical `510,780`-byte gzip
baseline is **Yes**, based on:

1. all five foundation pages are dynamic route entries;
2. AppShell remains the synchronous route boundary;
3. the default Home page no longer imports `EuiCodeBlock`/Prism;
4. the analyzer sums the manifest entry/static closure and default-route closure
   without double-counting shared chunks;
5. the analyzer fails if the real initial-route gzip is not below `510,780`;
6. a forward budget cannot be accepted until it is derived from a real build.

This is a feasibility judgment, not a measured performance result.

## Phase B command

```bash
cd frontend
npm ci
npm run verify:adj001-phase-b
```

If the measured value is below `510,780`, record the result, set a limited-headroom
`forwardInitialRouteGzipBytes` in `frontend/bundle-budget.json`, then run:

```bash
npm run verify:bundle-budget
npm run verify
```

If the value is not below `510,780`, continue evidence-driven splitting; do not
raise the threshold to make the check pass.

## Gate consequence

- WP-05: authorized to start.
- ADJ-001: Phase A accepted; Phase B deferred; not `Verified complete`.
- G2: cannot close until WP-05 passes and ADJ-001 Phase B passes.
- Production/performance acceptance: cannot cite an improved gzip number until
  the real build evidence exists.
- Business modules: remain frozen.

## Repository safety

No Pull Request, GitHub Actions, upstream write or force push is authorized by
this decision.
