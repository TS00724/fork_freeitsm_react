# Language Profiles

Use these profiles after repository discovery. Prefer project-defined scripts over generic commands.

## Rust

Detection: `Cargo.toml`, `Cargo.lock`, `rust-toolchain.toml`, `justfile`, `BUILD.bazel`.

- Format with the repository command, usually `cargo fmt` or `just fmt`.
- Prefer `cargo test -p <crate>` or the repository's scoped `just test` target over broad workspace runs during iteration.
- Run Clippy/fix commands only through the project convention when available; avoid slow workspace-wide linting unless the touched code is shared.
- Inline format arguments when supported, collapse unnecessary nested `if`s, prefer method references over redundant closures, and make `match` exhaustive when practical.
- Prefer private modules and explicit public exports.
- Avoid ambiguous boolean or `Option` positional parameters in public APIs; use enums/builders/named methods when it improves call-site readability.
- If using build-time file reads such as `include_str!`, `include_bytes!`, migrations, or embedded assets, update the build system data declarations as well as Cargo metadata.
- For traits returning async work, prefer explicit future contracts with required sendability instead of macro shortcuts when the project style supports it.

## Python

Detection: `pyproject.toml`, `requirements*.txt`, `uv.lock`, `poetry.lock`, `Pipfile`, `tox.ini`, `noxfile.py`.

- Treat Python 3 as the baseline unless the project explicitly says otherwise; do not add Python 2 compatibility shims.
- Use the project runner: `uv run`, `poetry run`, `hatch run`, `tox`, `nox`, or the existing Make/Just target.
- Common validation: `ruff format`, `ruff check`, `mypy`/`pyright`, and `pytest`, but use only commands configured by the repo.
- Prefer typed, small functions with explicit error boundaries. Avoid hidden global mutable state.
- Keep I/O, network, subprocess, and time dependencies injectable for tests.
- For CLI changes, update parser tests, help snapshots, and documentation if behavior changes.
- For packaging changes, update lockfiles and verify import paths/package data.

## Go

Detection: `go.mod`, `go.sum`, `Makefile`, `Taskfile.yml`.

- Always run `gofmt`/`go fmt` on changed packages.
- Prefer `go test ./path/...` for the changed package; run `go test ./...` when shared packages or public APIs changed.
- Use `go vet` or repository lint targets when defined.
- Pass `context.Context` through I/O, RPC, database, and long-running operations.
- Wrap errors with useful operation context; avoid swallowing errors or returning ambiguous sentinel values.
- Keep interfaces small and consumer-owned. Do not introduce interfaces solely for mocking when a concrete type is simpler.
- Avoid goroutine leaks: handle cancellation, close channels from the sender side, and document ownership.

## C

Detection: `CMakeLists.txt`, `Makefile`, `meson.build`, `configure.ac`, `.c`, `.h`.

- Preserve ABI/API compatibility unless explicitly changing it.
- Prefer explicit ownership comments for allocated memory, buffers, and handles.
- Check every allocation, syscall, and library call that can fail.
- Avoid undefined behavior: bounds, alignment, signed overflow, lifetime, aliasing, and null dereferences.
- Use existing formatting (`clang-format`, `indent`, or project style) and build targets.
- Add tests for boundary lengths, null/empty inputs, error paths, and resource cleanup.
- When practical, run sanitizer builds or the repository's valgrind/asan target for memory-sensitive changes.

## C++

Detection: `CMakeLists.txt`, `BUILD`, `WORKSPACE`, `vcpkg.json`, `conanfile.*`, `.cc`, `.cpp`, `.hpp`.

- Prefer RAII, value semantics, const-correctness, and standard library abstractions over manual ownership.
- Use smart pointers to express ownership; avoid raw owning pointers.
- Keep exception/no-exception policy consistent with the repository.
- Avoid template complexity unless it materially improves type safety or performance.
- Preserve ABI expectations for libraries. Be careful with virtual methods, exported symbols, struct layouts, and inline functions in headers.
- Validate with the project build/test/lint targets; consider sanitizer builds for memory/concurrency changes.

## JavaScript and TypeScript

Detection: `package.json`, `pnpm-lock.yaml`, `yarn.lock`, `package-lock.json`, `bun.lockb`, `tsconfig.json`, `eslint.config.*`, `vite.config.*`, `next.config.*`.

- Use the detected package manager: `pnpm`, `yarn`, `npm`, or `bun`; do not mix lockfiles.
- Prefer existing scripts: `lint`, `typecheck`, `test`, `build`, `format`, `e2e`.
- TypeScript: preserve or strengthen types; avoid `any` unless it is isolated and justified. Prefer discriminated unions for state machines and API variants.
- JavaScript: follow existing module system (`esm`/`cjs`) and runtime target.
- React/UI: keep state local where possible, use accessible controls, and update snapshots/visual tests when output changes.
- Backend Node: validate inputs at boundaries, handle async errors, and avoid unbounded queues, logs, or request bodies.
- Never edit generated clients or bundled output directly when a source schema/generator exists.

## Mixed-language repositories

- Identify the boundary being changed: FFI, CLI, HTTP/RPC API, generated bindings, schema, database, or build glue.
- Update both sides of the interface and regenerate clients or bindings.
- Validate the narrowest end-to-end path that crosses the boundary.
- Document the compatibility impact if wire format, config, ABI, or persistence changed.
