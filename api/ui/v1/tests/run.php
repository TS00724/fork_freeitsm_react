<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

ini_set('error_log', sys_get_temp_dir() . '/freeitsm-wp04-ui-api-test.log');

$tests = [];

function wp04Test(string $name, callable $test): void
{
    global $tests;
    $tests[] = [$name, $test];
}

function wp04Assert(bool $condition, string $message = 'Assertion failed'): void
{
    if (!$condition) throw new RuntimeException($message);
}

function wp04Same($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(($message === '' ? 'Values differ' : $message)
            . '\nExpected: ' . var_export($expected, true)
            . '\nActual: ' . var_export($actual, true));
    }
}

function wp04Json(UiApiHttpResponse $response): array
{
    $decoded = json_decode($response->body(), true);
    wp04Assert(is_array($decoded), 'Response must contain a JSON object. Body: ' . $response->body());
    return $decoded;
}

function wp04Server(string $method, string $path, array $headers = []): array
{
    $server = [
        'REQUEST_METHOD' => $method,
        'PATH_INFO' => $path,
        'REQUEST_URI' => '/api/ui/v1/index.php' . $path,
        'SCRIPT_NAME' => '/api/ui/v1/index.php',
    ];
    foreach ($headers as $name => $value) {
        $key = strtoupper(str_replace('-', '_', $name));
        if ($key === 'CONTENT_TYPE') {
            $server['CONTENT_TYPE'] = $value;
        } else {
            $server['HTTP_' . $key] = $value;
        }
    }
    return $server;
}

$foundationRoutes = require dirname(__DIR__) . '/lib/routes.php';
$testRoutes = array_merge($foundationRoutes, [
    new UiApiRoute('POST', '#^/echo$#', 'test.echo', static function (
        UiApiRequest $request,
        UiApiRequestContext $context,
        array $parameters
    ): array {
        unset($context, $parameters);
        return ['body' => $request->jsonObject()];
    }),
    new UiApiRoute('GET', '#^/items/(?P<id>[^/]+)$#', 'test.item', static function (
        UiApiRequest $request,
        UiApiRequestContext $context,
        array $parameters
    ): array {
        unset($request, $context);
        return ['id' => $parameters['id']];
    }, ['id' => 'positive_int']),
    new UiApiRoute('GET', '#^/boom$#', 'test.boom', static function (): array {
        throw new RuntimeException('SECRET_TOKEN at /private/server/path.php:99');
    }),
    new UiApiRoute('GET', '#^/status/401$#', 'test.401', static function (): array {
        throw UiApiException::unauthorized();
    }),
    new UiApiRoute('GET', '#^/status/403$#', 'test.403', static function (): array {
        throw UiApiException::forbidden();
    }),
    new UiApiRoute('GET', '#^/status/409$#', 'test.409', static function (): array {
        throw UiApiException::conflict('The resource changed.', ['field' => 'version']);
    }),
    new UiApiRoute('GET', '#^/status/422$#', 'test.422', static function (): array {
        throw UiApiException::validation('The command is invalid.', ['field' => 'title']);
    }),
    new UiApiRoute('GET', '#^/status/429$#', 'test.429', static function (): array {
        throw UiApiException::rateLimited(15);
    }),
]);

$kernel = uiApiBuildKernel($testRoutes);

wp04Test('GET / returns the versioned success envelope', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/'));
    wp04Same(200, $response->status());
    $json = wp04Json($response);
    wp04Same('FreeITSM Browser UI API', $json['data']['name']);
    wp04Same('1', $json['meta']['apiVersion']);
    wp04Same('application/json; charset=utf-8', $response->header('Content-Type'));
});

wp04Test('GET /health is process-only and makes no dependency claim', static function () use ($kernel): void {
    $json = wp04Json($kernel->handleServer(wp04Server('GET', '/health')));
    wp04Same('ok', $json['data']['status']);
    wp04Same('not_checked', $json['data']['checks']['database']);
    wp04Same('not_checked', $json['data']['checks']['session']);
});

wp04Test('HEAD uses GET routing and emits no body', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('HEAD', '/'));
    wp04Same(200, $response->status());
    wp04Same('', $response->body());
});

wp04Test('OPTIONS returns a route-specific Allow header', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('OPTIONS', '/health'));
    wp04Same(204, $response->status());
    wp04Same('GET, HEAD, OPTIONS', $response->header('Allow'));
    wp04Same('', $response->body());
});

wp04Test('an unknown path returns 404 with the common envelope', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/missing'));
    wp04Same(404, $response->status());
    $json = wp04Json($response);
    wp04Same('not_found', $json['error']['code']);
    wp04Assert(isset($json['meta']['requestId'], $json['meta']['correlationId']));
});

wp04Test('a known route with POST returns 405 and Allow', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('POST', '/health'));
    wp04Same(405, $response->status());
    wp04Same('GET, HEAD, OPTIONS', $response->header('Allow'));
    wp04Same('method_not_allowed', wp04Json($response)['error']['code']);
});

wp04Test('TRACE is not silently accepted', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('TRACE', '/'));
    wp04Same(405, $response->status());
});

wp04Test('malformed JSON returns 400', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('POST', '/echo', ['Content-Type' => 'application/json']), [], '{oops');
    wp04Same(400, $response->status());
    wp04Same('invalid_json', wp04Json($response)['error']['code']);
});

wp04Test('a top-level JSON array is rejected', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('POST', '/echo', ['Content-Type' => 'application/json']), [], '[]');
    wp04Same(400, $response->status());
});

wp04Test('a top-level JSON scalar is rejected', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('POST', '/echo', ['Content-Type' => 'application/json']), [], 'true');
    wp04Same(400, $response->status());
});

wp04Test('unsupported Content-Type returns 415', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('POST', '/echo', ['Content-Type' => 'text/plain']), [], '{}');
    wp04Same(415, $response->status());
    wp04Same('unsupported_media_type', wp04Json($response)['error']['code']);
});

wp04Test('application/json with parameters is accepted', static function () use ($kernel): void {
    $response = $kernel->handleServer(
        wp04Server('POST', '/echo', ['Content-Type' => 'application/json; charset=utf-8']),
        [],
        '{"name":"test"}'
    );
    wp04Same(200, $response->status());
    wp04Same('test', wp04Json($response)['data']['body']['name']);
});

wp04Test('application vendor +json is accepted', static function () use ($kernel): void {
    $response = $kernel->handleServer(
        wp04Server('POST', '/echo', ['Content-Type' => 'application/vnd.freeitsm+json']),
        [],
        '{"accepted":true}'
    );
    wp04Same(200, $response->status());
    wp04Same(true, wp04Json($response)['data']['body']['accepted']);
});

wp04Test('an empty optional command body becomes an empty object', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('POST', '/echo'), [], '');
    wp04Same([], wp04Json($response)['data']['body']);
});

wp04Test('a valid request ID is propagated into header and body', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/', ['X-Request-ID' => 'request-123']));
    $json = wp04Json($response);
    wp04Same('request-123', $response->header('X-Request-ID'));
    wp04Same('request-123', $json['meta']['requestId']);
});

wp04Test('an invalid request ID is replaced instead of reflected', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/', ['X-Request-ID' => "bad\r\nid"]));
    wp04Assert($response->header('X-Request-ID') !== "bad\r\nid");
    wp04Assert(preg_match('/^[A-Fa-f0-9-]{36}$/', (string)$response->header('X-Request-ID')) === 1);
});

wp04Test('a valid correlation ID is propagated', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/', [
        'X-Request-ID' => 'request-1',
        'X-Correlation-ID' => 'correlation-1',
    ]));
    wp04Same('correlation-1', $response->header('X-Correlation-ID'));
    wp04Same('correlation-1', wp04Json($response)['meta']['correlationId']);
});

wp04Test('correlation ID defaults to request ID', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/', ['X-Request-ID' => 'request-2']));
    wp04Same('request-2', $response->header('X-Correlation-ID'));
});

wp04Test('envelope IDs exactly match response headers', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/missing'));
    $json = wp04Json($response);
    wp04Same($response->header('X-Request-ID'), $json['meta']['requestId']);
    wp04Same($response->header('X-Correlation-ID'), $json['meta']['correlationId']);
});

wp04Test('a raw backslash in the path is rejected', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/bad\\path'));
    wp04Same(400, $response->status());
    wp04Same('invalid_path', wp04Json($response)['error']['code']);
});

wp04Test('an encoded slash in a segment is rejected', static function () use ($kernel): void {
    wp04Same(400, $kernel->handleServer(wp04Server('GET', '/bad%2fpath'))->status());
});

wp04Test('a double-encoded backslash is rejected', static function () use ($kernel): void {
    wp04Same(400, $kernel->handleServer(wp04Server('GET', '/bad%255cpath'))->status());
});

wp04Test('a dot segment is rejected before routing', static function () use ($kernel): void {
    wp04Same(400, $kernel->handleServer(wp04Server('GET', '/../health'))->status());
});

wp04Test('invalid percent encoding is rejected', static function () use ($kernel): void {
    wp04Same(400, $kernel->handleServer(wp04Server('GET', '/bad%ZZ'))->status());
});

wp04Test('an invalid typed route parameter returns 400', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/items/not-an-id'));
    wp04Same(400, $response->status());
    wp04Same('invalid_route_parameter', wp04Json($response)['error']['code']);
});

wp04Test('a valid typed route parameter is coerced', static function () use ($kernel): void {
    $json = wp04Json($kernel->handleServer(wp04Server('GET', '/items/42')));
    wp04Same(42, $json['data']['id']);
});

wp04Test('an internal exception does not leak message, path or stack', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/boom'));
    wp04Same(500, $response->status());
    $body = $response->body();
    wp04Assert(strpos($body, 'SECRET_TOKEN') === false);
    wp04Assert(strpos($body, '/private/server/path.php') === false);
    wp04Assert(strpos(strtolower($body), 'trace') === false);
    wp04Same('server_error', wp04Json($response)['error']['code']);
});

wp04Test('401 contract semantics use unauthenticated', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/status/401'));
    wp04Same(401, $response->status());
    wp04Same('unauthenticated', wp04Json($response)['error']['code']);
});

wp04Test('403 contract semantics use forbidden', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/status/403'));
    wp04Same(403, $response->status());
    wp04Same('forbidden', wp04Json($response)['error']['code']);
});

wp04Test('409 contract semantics preserve safe conflict detail', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/status/409'));
    wp04Same(409, $response->status());
    wp04Same('version', wp04Json($response)['error']['details']['field']);
});

wp04Test('422 contract semantics use validation_failed', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/status/422'));
    wp04Same(422, $response->status());
    wp04Same('validation_failed', wp04Json($response)['error']['code']);
});

wp04Test('429 includes Retry-After', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/status/429'));
    wp04Same(429, $response->status());
    wp04Same('15', $response->header('Retry-After'));
});

wp04Test('responses never grant wildcard CORS', static function () use ($kernel): void {
    $response = $kernel->handleServer(wp04Server('GET', '/'));
    wp04Same(null, $response->header('Access-Control-Allow-Origin'));
    wp04Same(null, $response->header('Access-Control-Allow-Credentials'));
});

wp04Test('production PHP does not start Session, DB or machine API auth', static function (): void {
    $root = dirname(__DIR__);
    $phpFiles = array_merge(glob($root . '/*.php') ?: [], glob($root . '/lib/*.php') ?: []);
    $source = '';
    foreach ($phpFiles as $file) $source .= file_get_contents($file) ?: '';
    wp04Assert(stripos($source, 'session_start') === false, 'WP-04 must not start a Session.');
    wp04Assert(stripos($source, 'HTTP_AUTHORIZATION') === false, 'WP-04 must not inspect machine API auth.');
    wp04Assert(stripos($source, 'Bearer ') === false, 'WP-04 must not use Bearer API keys.');
    wp04Assert(stripos($source, "require_once __DIR__ . '/../../../../config.php'") === false, 'WP-04 must not load DB config.');
});

wp04Test('OpenAPI source declares every required status semantic', static function (): void {
    $contractPath = dirname(__DIR__) . '/openapi.json';
    $contract = json_decode((string)file_get_contents($contractPath), true);
    wp04Assert(is_array($contract));
    wp04Same('3.1.0', $contract['openapi']);
    foreach (['400', '401', '403', '404', '405', '409', '415', '422', '429', '500'] as $status) {
        wp04Assert(isset($contract['x-freeitsm-status-semantics'][$status]), 'Missing status semantic ' . $status);
    }
    wp04Assert(isset($contract['components']['schemas']['UiApiErrorEnvelope']));
});

$failures = 0;
foreach ($tests as $index => $entry) {
    try {
        $entry[1]();
        echo 'PASS ' . str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) . ' ' . $entry[0] . PHP_EOL;
    } catch (Throwable $error) {
        $failures++;
        echo 'FAIL ' . str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) . ' ' . $entry[0] . PHP_EOL;
        echo '     ' . $error->getMessage() . PHP_EOL;
    }
}

echo sprintf('%d tests, %d failures', count($tests), $failures) . PHP_EOL;
exit($failures === 0 ? 0 : 1);
