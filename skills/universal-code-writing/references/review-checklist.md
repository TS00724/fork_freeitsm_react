# Review Checklist

Use this before finalizing a substantial patch or when asked to review code.

## Correctness

- The change directly addresses the requested behavior.
- Edge cases are handled: empty input, malformed input, large input, cancellation/timeouts, duplicate calls, retries, and partial failures.
- Errors include enough context for diagnosis without leaking secrets.
- Resource lifetime is clear: files, sockets, database transactions, goroutines/threads, timers, buffers, locks, and temporary directories.

## Compatibility

- Public APIs, CLI flags, config keys, database schemas, wire formats, generated clients, and saved state remain compatible or have an explicit migration plan.
- Defaults preserve existing behavior unless the user requested a breaking change.
- Feature flags, version gates, and rollout paths are considered for risky behavior changes.

## Security and privacy

- No secrets, tokens, keys, passwords, or private data are committed or logged.
- Inputs crossing trust boundaries are validated or parsed safely.
- Network, filesystem, subprocess, deserialization, template, and SQL paths avoid injection vulnerabilities.
- Permissions are least-privilege and destructive actions require explicit approval.

## Maintainability

- The diff is scoped and reviewable.
- Names explain domain intent rather than implementation mechanics.
- Complexity is localized; central orchestration files are not enlarged unnecessarily.
- Comments document invariants, tradeoffs, and non-obvious behavior rather than restating code.
- Tests are meaningful and not coupled to incidental implementation details.

## Validation

- Formatter ran for changed files or relevant package.
- Targeted tests ran for the touched component.
- Lint/typecheck/build ran when project convention requires it.
- UI snapshots/goldens were reviewed when output changed.
- Any skipped validation has an explicit reason and a concrete command for later execution.
