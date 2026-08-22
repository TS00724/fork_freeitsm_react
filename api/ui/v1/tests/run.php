<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use FreeITSM\UiApi\V1\UiApiException;
use FreeITSM\UiApi\V1\UiApiKernel;
use FreeITSM\UiApi\V1\UiRequest;
use FreeITSM\UiApi\V1\UiRequestContext;
use FreeITSM\UiApi\V1\UiResponseFactory;
use FreeITSM\UiApi\V1\UiRoute;
use FreeITSM\UiApi\V1\UiRouteResult;
use FreeITSM\UiApi\V1\UiRouter;

$passed = 0;
$failed = 0;

function testCase(string $name, callable $test): void
{
    global $passed, $failed;
    try {
        $test();
        $passed++;
        echo "PASS {$name}\n";
    } catch (Throwable $e) {
        $failed++;
        fwrite(STDERR, "FAIL {$name}: {$e->getMessage()}\n");
    }
}

function assertTrue($condition, string $message = 'Assertion failed'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertSameValue($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message !== '' ? $message : 'Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

/** @return array<string,mixed> */
function decodeResponse($response): array
{
    $decoded = json_decode($response->body(), true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Response is not a JSON object.');
    }
    return $decoded;
}

/** @param array<int,UiRoute> $routes */
function testKernel(array $routes): UiApiKernel
{
    return new UiApiKernel(new UiRouter($routes), new UiResponseFactory());
}

$kernel = freeitsmUiApiKernel();

testCase('root success envelope and metadata', function () use ($kernel): void {
    $response = $kernel->handle(new UiRequest('GET', '/', ['X-Request-ID' => 'req-123', 'X-Correlation-ID' => 'corr-456']));
    assertSameValue(200, $response->status());
    $body = decodeResponse($response);
    assertSameValue('FreeITSM UI API', $body['data']['name']);
    assertSameValue('req-123', $body['meta']['requestId']);
    assertSameValue('corr-456', $body['meta']['correlationId']);
    assertSameValue('1', $body['meta']['apiVersion']);
    assertSameValue('req-123', $response->headers()['X-Request-ID']);
    assertSameValue('corr-456', $response->headers()['X-Correlation-ID']);
    assertTrue(!array_key_exists('error', $body));
});

testCase('health route is database and session independent', function () use ($kernel): void {
    $response = $kernel->handle(new UiRequest('GET', '/health'));
    assertSameValue(200, $response->status());
    $body = decodeResponse($response);
    assertSameValue('ok', $body['data']['status']);
    assertSameValue('freeitsm-ui-api', $body['data']['service']);
});

testCase('HEAD reuses GET route contract', function () use ($kernel): void {
    $response = $kernel->handle(new UiRequest('HEAD', '/health'));
    assertSameValue(200, $response->status());
    assertSameValue('application/json; charset=utf-8', $response->headers()['Content-Type']);
});

testCase('unknown route uses stable error envelope', function () use ($kernel): void {
    $response = $kernel->handle(new UiRequest('GET', '/missing'));
    assertSameValue(404, $response->status());
    $body = decodeResponse($response);
    assertSameValue('not_found', $body['error']['code']);
    assertTrue(isset($body['meta']['requestId']));
    assertTrue(isset($body['meta']['correlationId']));
});

testCase('method mismatch returns 405 and Allow', function () use ($kernel): void {
    $response = $kernel->handle(new UiRequest('POST', '/health'));
    assertSameValue(405, $response->status());
    assertTrue(strpos($response->headers()['Allow'], 'GET') !== false);
    assertTrue(strpos($response->headers()['Allow'], 'HEAD') !== false);
    assertSameValue('method_not_allowed', decodeResponse($response)['error']['code']);
});

testCase('OPTIONS returns 204 without CORS wildcard', function () use ($kernel): void {
    $response = $kernel->handle(new UiRequest('OPTIONS', '/health'));
    assertSameValue(204, $response->status());
    assertSameValue('', $response->body());
    assertTrue(strpos($response->headers()['Allow'], 'OPTIONS') !== false);
    assertTrue(!array_key_exists('Access-Control-Allow-Origin', $response->headers()));
});

$jsonRoute = new UiRoute('POST', '/parse', 'testParse', function (UiRequest $request, UiRequestContext $context, array $params): UiRouteResult {
    return UiRouteResult::success(['received' => $request->jsonBody()]);
});
$jsonKernel = testKernel([$jsonRoute]);

testCase('malformed JSON is 400', function () use ($jsonKernel): void {
    $response = $jsonKernel->handle(new UiRequest('POST', '/parse', ['Content-Type' => 'application/json'], '{'));
    assertSameValue(400, $response->status());
    assertSameValue('invalid_json', decodeResponse($response)['error']['code']);
});

testCase('JSON arrays are rejected', function () use ($jsonKernel): void {
    $response = $jsonKernel->handle(new UiRequest('POST', '/parse', ['Content-Type' => 'application/json'], '[]'));
    assertSameValue(400, $response->status());
    assertSameValue('invalid_json', decodeResponse($response)['error']['code']);
});

testCase('unsupported Content-Type is 415', function () use ($jsonKernel): void {
    $response = $jsonKernel->handle(new UiRequest('POST', '/parse', ['Content-Type' => 'text/plain'], '{"ok":true}'));
    assertSameValue(415, $response->status());
    assertSameValue('unsupported_media_type', decodeResponse($response)['error']['code']);
});

testCase('valid vendor JSON object is parsed', function () use ($jsonKernel): void {
    $response = $jsonKernel->handle(new UiRequest('POST', '/parse', ['Content-Type' => 'application/problem+json'], '{"ok":true}'));
    assertSameValue(200, $response->status());
    assertSameValue(true, decodeResponse($response)['data']['received']['ok']);
});

testCase('oversized JSON is 413', function () use ($jsonKernel): void {
    $response = $jsonKernel->handle(new UiRequest('POST', '/parse', ['Content-Type' => 'application/json'], '{"value":"' . str_repeat('a', 1048576) . '"}'));
    assertSameValue(413, $response->status());
    assertSameValue('payload_too_large', decodeResponse($response)['error']['code']);
});

$intRoute = new UiRoute('GET', '/items/{id:int}', 'testItem', function (UiRequest $request, UiRequestContext $context, array $params): UiRouteResult {
    return UiRouteResult::success(['id' => $params['id']]);
});
$intKernel = testKernel([$intRoute]);

testCase('invalid typed route parameter is 400', function () use ($intKernel): void {
    $response = $intKernel->handle(new UiRequest('GET', '/items/not-an-int'));
    assertSameValue(400, $response->status());
    assertSameValue('invalid_route_parameter', decodeResponse($response)['error']['code']);
});

testCase('overflowing typed route parameter is 400', function () use ($intKernel): void {
    $response = $intKernel->handle(new UiRequest('GET', '/items/999999999999999999999999999'));
    assertSameValue(400, $response->status());
    assertSameValue('invalid_route_parameter', decodeResponse($response)['error']['code']);
});

testCase('valid typed route parameter is converted', function () use ($intKernel): void {
    $response = $intKernel->handle(new UiRequest('GET', '/items/42'));
    assertSameValue(42, decodeResponse($response)['data']['id']);
});

$explodeRoute = new UiRoute('GET', '/explode', 'testExplode', function (UiRequest $request, UiRequestContext $context, array $params): UiRouteResult {
    throw new RuntimeException('/srv/private/config.php secret-token-123');
});
$explodeKernel = testKernel([$explodeRoute]);

testCase('internal exception does not leak stack, path or secret', function () use ($explodeKernel): void {
    $response = $explodeKernel->handle(new UiRequest('GET', '/explode'));
    assertSameValue(500, $response->status());
    assertTrue(strpos($response->body(), '/srv/private') === false);
    assertTrue(strpos($response->body(), 'secret-token-123') === false);
    assertSameValue('server_error', decodeResponse($response)['error']['code']);
});

testCase('unsafe response header cannot escape into the response', function (): void {
    $route = new UiRoute('GET', '/unsafe-header', 'testUnsafeHeader', function (UiRequest $request, UiRequestContext $context, array $params): UiRouteResult {
        return UiRouteResult::success(['ok' => true], 200, [], ['X-Test' => "safe\r\nInjected: yes"]);
    });
    $response = testKernel([$route])->handle(new UiRequest('GET', '/unsafe-header'));
    assertSameValue(500, $response->status());
    assertTrue(!array_key_exists('Injected', $response->headers()));
    assertSameValue('server_error', decodeResponse($response)['error']['code']);
});

foreach ([401 => 'unauthenticated', 403 => 'forbidden', 409 => 'conflict', 422 => 'validation_failed', 429 => 'rate_limited'] as $status => $code) {
    testCase("HTTP {$status} semantic envelope", function () use ($status, $code): void {
        $route = new UiRoute('GET', '/status', 'testStatus', function (UiRequest $request, UiRequestContext $context, array $params) use ($status, $code): UiRouteResult {
            throw new UiApiException($status, $code, 'Expected semantic test error.');
        });
        $response = testKernel([$route])->handle(new UiRequest('GET', '/status'));
        assertSameValue($status, $response->status());
        assertSameValue($code, decodeResponse($response)['error']['code']);
    });
}

testCase('invalid request ID is rejected and replaced in error metadata', function () use ($kernel): void {
    $response = $kernel->handle(new UiRequest('GET', '/', ['X-Request-ID' => "bad\nheader"]));
    assertSameValue(400, $response->status());
    $body = decodeResponse($response);
    assertSameValue('invalid_request_id', $body['error']['code']);
    assertTrue($body['meta']['requestId'] !== "bad\nheader");
});

testCase('invalid correlation ID is rejected', function () use ($kernel): void {
    $response = $kernel->handle(new UiRequest('GET', '/', ['X-Correlation-ID' => 'bad value']));
    assertSameValue(400, $response->status());
    assertSameValue('invalid_request_id', decodeResponse($response)['error']['code']);
});

testCase('path traversal and encoded separators are rejected', function (): void {
    foreach (['/../health', '/%252e%252e/health', '/a%2Fb', '/a%255cb', '/bad%GG'] as $path) {
        try {
            new UiRequest('GET', $path);
            throw new RuntimeException("Path {$path} was accepted.");
        } catch (UiApiException $e) {
            assertSameValue(400, $e->httpStatus());
        }
    }
});

testCase('OpenAPI and production route table agree', function () use ($kernel): void {
    $spec = json_decode((string)file_get_contents(dirname(__DIR__) . '/openapi.json'), true);
    assertTrue(is_array($spec));
    assertSameValue('3.1.0', $spec['openapi']);
    $contractRoutes = [];
    foreach ($spec['paths'] as $path => $item) {
        foreach ($item as $method => $operation) {
            if (is_array($operation) && isset($operation['operationId'])) {
                $contractRoutes[strtoupper($method) . ' ' . $path] = $operation['operationId'];
            }
        }
    }
    $runtimeRoutes = [];
    foreach ($kernel->router()->routes() as $route) {
        $runtimeRoutes[$route->method() . ' ' . $route->template()] = $route->name();
    }
    ksort($contractRoutes);
    ksort($runtimeRoutes);
    assertSameValue($runtimeRoutes, $contractRoutes);
    assertTrue(isset($spec['components']['schemas']['UiApiErrorResponse']));
    assertTrue(isset($spec['components']['responses']['Unauthorized']));
    assertTrue(isset($spec['components']['responses']['Forbidden']));
});

testCase('UI API source has no wildcard CORS, machine key, session or database bootstrap', function (): void {
    $root = dirname(__DIR__);
    $source = '';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/lib'));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $source .= (string)file_get_contents($file->getPathname());
        }
    }
    assertTrue(strpos($source, 'Access-Control-Allow-Origin: *') === false);
    assertTrue(stripos($source, 'X-Api-Key') === false);
    assertTrue(stripos($source, 'Bearer fitsm_') === false);
    assertTrue(stripos($source, 'session_start') === false);
    assertTrue(stripos($source, 'connectToDatabase') === false);
});

echo "\n{$passed} passed, {$failed} failed\n";
exit($failed === 0 ? 0 : 1);
