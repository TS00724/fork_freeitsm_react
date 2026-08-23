---
name: universal-code-writing
description: Cross-language code implementation, refactoring, debugging, test authoring, and code review workflow for Codex across Rust, Python, Go, C, C++, JavaScript, TypeScript, and mixed-language repositories. Use when modifying source code, adding features, fixing bugs, designing APIs, updating tests, hardening CI, reviewing diffs, updating dependencies, or preparing production-quality patches.
---

# Universal Code Writing

Use this skill to produce small, idiomatic, testable, reviewable code changes across languages and frameworks.

## Operating contract

- Treat repository-local instructions as authoritative: read `AGENTS.md`, `README`, contribution docs, package manifests, build files, CI config, and nearby code before changing implementation.
- Prefer the repository's existing tools, style, architecture, naming, error handling, logging, and test patterns over generic preferences.
- Make the smallest coherent change that solves the user's request. Avoid broad rewrites, drive-by formatting, unrelated dependency upgrades, or large architectural moves unless the task requires them.
- Keep public API surfaces narrow. Avoid boolean-trap or ambiguous positional arguments when a named option, enum, builder, newtype, or explicit method would make call sites clearer.
- Avoid adding trivial helper functions or modules used only once unless they isolate a real invariant, clarify a complex block, enable testing, or reduce meaningful duplication.
- Keep modules maintainable. When a file is already large or high-touch, prefer adding a focused sibling module/file instead of extending the central orchestration file.
- Preserve generated-code boundaries: find the generator, schema, IDL, migration source, or template; update source inputs and regenerate outputs rather than hand-editing generated artifacts.
- Never introduce secrets, credentials, production endpoints, telemetry leakage, or destructive commands. Ask before irreversible writes, production migrations, network-heavy work, or risky dependency changes.

## Default workflow

1. **Discover**
   - Identify the language stack and package manager from files such as `Cargo.toml`, `pyproject.toml`, `go.mod`, `CMakeLists.txt`, `package.json`, lockfiles, Makefiles, Justfiles, and CI workflows.
   - Locate relevant tests before editing. Search for existing helpers, fixtures, mocks, snapshots, and integration patterns.
   - If the task is under-specified, inspect the codebase first and make a defensible choice. Ask only when credentials, product intent, or destructive operations are truly blocked.

2. **Plan the patch**
   - State the intended files and validation path when the change is non-trivial.
   - Split large work into reviewable stages. As a rule of thumb, keep non-mechanical changes below about 800 changed lines; complex logic changes should be much smaller when possible.
   - For external interfaces, explicitly check compatibility impact: public APIs, CLI arguments, config files, database schemas, wire formats, generated clients, migrations, saved sessions, and plugin/extension contracts.

3. **Implement**
   - Follow local idioms for errors, async/concurrency, dependency injection, resource cleanup, logging, and configuration.
   - Prefer explicit, typed, bounded data structures over stringly-typed or unbounded state.
   - Keep context and logs bounded. Do not inject unbounded file contents, history, traces, or user data into prompts, caches, telemetry, or model-visible payloads.
   - Update docs only when the public behavior, setup, API, or operator workflow changes. Avoid generic documentation churn.

4. **Test and validate**
   - Run formatter and targeted tests for the changed component first.
   - Run lint/typecheck/build commands that the repository already defines.
   - Run broader suites only when the touched area is shared or the project convention requires it; ask before very slow, expensive, or environment-sensitive full-suite runs.
   - For UI/text output changes, update snapshots/golden files intentionally and review the diff.
   - When validation cannot run, record the exact command that should be run and the blocker.

5. **Final response discipline**
   - Summarize what changed and why.
   - List validation commands run and their result.
   - List validation not run and why.
   - Call out migration, compatibility, security, or deployment risks.

## Test authoring rules

- Prefer tests that exercise externally observable behavior rather than internal implementation details.
- For agent/workflow/protocol/UI behavior, prefer integration or end-to-end tests over narrow unit tests.
- For pure algorithms, parsers, serializers, and boundary cases, unit tests are appropriate.
- Test whole objects or structured outputs where possible instead of asserting one field at a time.
- Do not add tests for constants, statically defined values, or behavior that has intentionally been removed.
- Keep test-only helpers out of main implementation unless there is a clear production benefit.
- Avoid mutating global process state in tests; pass environment-derived dependencies explicitly when possible.

## Dependency and tool changes

- Before adding a dependency, check whether an existing dependency or standard library facility is sufficient.
- If a manifest changes, update the corresponding lockfile and any generated dependency metadata required by the build system.
- Prefer scoped tool invocations (`package`, `crate`, `module`, or changed test file) over workspace-wide commands during iteration.
- Do not install global tools unless the repository explicitly relies on them and no local alternative exists.

## Language-specific details

Read `references/language-profiles.md` when the repository uses Rust, Python, Go, C, C++, JavaScript, or TypeScript and you need concrete tooling or idiom guidance.

Read `references/review-checklist.md` before finalizing a substantial patch or reviewing a diff.

Use `assets/AGENTS.md.template` when the user asks you to create project-level Codex instructions for a repository.
