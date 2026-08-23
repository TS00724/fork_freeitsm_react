# Task Templates

Use these compact templates when guiding another Codex instance.

## Feature implementation prompt

Use the `universal-code-writing` skill. Implement <feature> in this repository. First inspect repository instructions and existing patterns. Make the smallest coherent patch, add or update tests for externally visible behavior, run targeted validation, and summarize changed files plus commands run.

## Bug fix prompt

Use the `universal-code-writing` skill. Reproduce or localize <bug>. Find the smallest root-cause fix, add a regression test, run targeted validation, and report any remaining risk or validation that could not run.

## Refactor prompt

Use the `universal-code-writing` skill. Refactor <area> without changing behavior. Preserve public interfaces unless explicitly required. Add tests only if existing coverage cannot protect the refactor. Run formatter and targeted tests, then summarize behavior-preservation evidence.

## Code review prompt

Use the `universal-code-writing` skill. Review this diff for correctness, compatibility, security, maintainability, and validation gaps. Prioritize issues that can cause real defects. Avoid style-only comments unless they affect clarity or future maintenance.
