# Governance audit — universal-code-writing adoption

Date: 2026-08-23  
Base `main`: `c1dd99792c1e31eae55341126c3dd0fde3f0b4cb`  
Branch: `governance-universal-code-writing`

## Scope

Documentation/governance only. No React, PHP, BFF, routing, package manifest, lockfile, database, workflow or business-feature implementation is changed by this adoption.

## Sources adopted

- `skills/universal-code-writing/SKILL.md`
- `skills/universal-code-writing/references/language-profiles.md`
- `skills/universal-code-writing/references/review-checklist.md`
- `skills/universal-code-writing/references/task-templates.md`
- `skills/universal-code-writing/assets/AGENTS.md.template`

FreeITSM-specific instructions are in root `AGENTS.md`; the generic skill text remains generic.

## Review-checklist audit

### Correctness

- `WORK_PROGRESS.md` points to the checked-in skill and audit paths.
- ADJ-001 is inserted before WP-05 without renumbering the 37 work packages.
- The known 510.78 kB gzip baseline remains visible and unresolved until measured by ADJ-001.

### Compatibility

- No runtime/public API/database/wire/generated contract changes.
- WP-04 remains verified complete.
- WP-05 remains not started.
- Existing candidate performance branch is referenced for inspection only and is not merged by this governance change.

### Security/privacy

- No secrets, credentials, endpoints or private data added.
- Existing no-PR/no-Actions/no-upstream-write/no-force rules are retained and repeated in `AGENTS.md`.

### Maintainability

- Generic workflow stays under `skills/universal-code-writing/`.
- Project-specific thresholds stay in root `AGENTS.md` and the active adjustment task.
- LOC limits explicitly forbid mechanical `part1`/`part2` slicing and require responsibility-based boundaries.

### Validation

Repository API reads verified the expected base and branch ancestry. Compare review confirms the governance branch is fast-forward from the expected base and contains governance/documentation files only. No package/runtime command is claimed because this patch does not change executable source; ADJ-001 requires the real `npm ci && npm run verify` runtime evidence before any performance remediation is accepted.

## Merge rule

Re-read `main`; only if it remains `c1dd99792c1e31eae55341126c3dd0fde3f0b4cb`, update it to the governance branch head with `force=false`. Do not create a Pull Request.
