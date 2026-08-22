#!/usr/bin/env bash
set -euo pipefail

API_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DOCUMENT_ROOT="$(cd "$API_ROOT/../../.." && pwd)"
HOST="${WP04_SMOKE_HOST:-127.0.0.1}"
PORT="${WP04_SMOKE_PORT:-18094}"
BASE="http://${HOST}:${PORT}/api/ui/v1"
SERVER_LOG="$(mktemp)"
RESPONSE="$(mktemp)"

cleanup() {
  if [[ -n "${SERVER_PID:-}" ]]; then
    kill "$SERVER_PID" >/dev/null 2>&1 || true
    wait "$SERVER_PID" 2>/dev/null || true
  fi
  rm -f "$SERVER_LOG" "$RESPONSE"
}
trap cleanup EXIT

php -S "${HOST}:${PORT}" -t "$DOCUMENT_ROOT" >"$SERVER_LOG" 2>&1 &
SERVER_PID=$!

for _ in $(seq 1 50); do
  if curl --silent --show-error --fail "${BASE}/index.php/health" >/dev/null 2>&1; then
    break
  fi
  sleep 0.1
done

request() {
  curl --silent --show-error --include "$@" >"$RESPONSE"
}

assert_contains() {
  local expected="$1"
  if ! grep -Fqi -- "$expected" "$RESPONSE"; then
    echo "Expected response to contain: $expected" >&2
    cat "$RESPONSE" >&2
    exit 1
  fi
}

assert_absent() {
  local unexpected="$1"
  if grep -Fqi -- "$unexpected" "$RESPONSE"; then
    echo "Response unexpectedly contained: $unexpected" >&2
    cat "$RESPONSE" >&2
    exit 1
  fi
}

request "${BASE}/index.php/"
assert_contains 'HTTP/1.1 200 OK'
assert_contains '"name":"FreeITSM UI API"'
assert_absent 'X-Powered-By:'
assert_absent 'Access-Control-Allow-Origin:'

request -H 'X-Request-ID: req-smoke-1' -H 'X-Correlation-ID: corr-smoke-1' \
  "${BASE}/index.php/health"
assert_contains 'HTTP/1.1 200 OK'
assert_contains 'X-Request-ID: req-smoke-1'
assert_contains 'X-Correlation-ID: corr-smoke-1'
assert_contains '"status":"ok"'

request -X OPTIONS "${BASE}/index.php/health"
assert_contains 'HTTP/1.1 204 No Content'
assert_contains 'Allow: GET, HEAD, OPTIONS'
assert_absent 'Content-Type:'
assert_absent 'Access-Control-Allow-Origin:'

request -X POST -H 'Content-Type: application/json' --data '{}' \
  "${BASE}/index.php/health"
assert_contains 'HTTP/1.1 405 Method Not Allowed'
assert_contains 'Allow: GET, HEAD, OPTIONS'
assert_contains '"code":"method_not_allowed"'

request "${BASE}/index.php/does-not-exist"
assert_contains 'HTTP/1.1 404 Not Found'
assert_contains '"code":"not_found"'

curl --silent --show-error --fail "${BASE}/openapi.json" \
  | php -r '
      $spec = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
      if (($spec["openapi"] ?? null) !== "3.1.0" || ($spec["info"]["version"] ?? null) !== "1.0.0") {
          fwrite(STDERR, "Unexpected OpenAPI document.\n");
          exit(1);
      }
    '

echo 'WP-04 UI API HTTP smoke: PASS'
