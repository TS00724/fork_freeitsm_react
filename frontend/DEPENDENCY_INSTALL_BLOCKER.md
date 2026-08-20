# Dependency installation blocker

Observed on 2026-08-20 in the implementation environment:

```text
npm install --package-lock-only --ignore-scripts --fetch-retries=0 --fetch-timeout=3000
npm http fetch GET https://registry.npmjs.org/@elastic%2feui failed with EAI_AGAIN
```

A package lock was not fabricated or copied from another project. Until registry
access is restored, the following remain unverified:

- package resolution and peer compatibility in this exact project;
- `npm ci`;
- strict TypeScript module/type resolution;
- ESLint;
- Vitest component tests;
- Vite production build and preview runtime.

Unblock procedure:

```bash
cd frontend
npm install --package-lock-only --ignore-scripts
# Review package-lock.json and package/license changes before committing it.
npm ci
npm run verify
npm run dev -- --host 127.0.0.1
```

After the lockfile is reviewed, update `progress/VERIFICATION_REPORT.md`,
`progress/WORK_PROGRESS.md`, and `handoffs/WP-02.md`. WP-02 must not be marked
Verified complete before these commands pass.
