<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';
ini_set('error_log', sys_get_temp_dir() . '/freeitsm-wp04-ui-api-test.log');

$tests = [];
function wp04Test(string $name, callable $test): void { global $tests; $tests[] = [$name, $test]; }
function wp04Assert(bool $condition, string $message = 'Assertion failed'): void { if (!$condition) throw new RuntimeException($message); }
function wp04Same($expected, $actual, string $message = ''): void {
    if ($expected !== $actual) throw new RuntimeException(($message === '' ? 'Values differ' : $message)
        . '\nExpected: ' . var_export($expected, true) . '\nActual: ' . var_export($actual, true));
}
function wp04Json(UiApiHttpResponse $response): array {
    $decoded = json_decode($response->body(), true);
    wp04Assert(is_array($decoded), 'Response must contain a JSON object. Body: ' . $response->body());
    return $decoded;
}
function wp04Server(string $method, string $path, array $headers = []): array {
    $server = ['REQUEST_METHOD' => $method, 'PATH_INFO' => $path, 'REQUEST_URI' => '/api/ui/v1/index.php' . $path, 'SCRIPT_NAME' => '/api/ui/v1/index.php'];
    foreach ($headers as $name => $value) {
        $key = strtoupper(str_replace('-', '_', $name));
        if ($key === 'CONTENT_TYPE') $server['CONTENT_TYPE'] = $value; else $server['HTTP_' . $key] = $value;
    }
    return $server;
}

$routes = array_merge(require dirname(__DIR__) . '/lib/routes.php', [
    new UiApiRoute('POST', '#^/echo$#', 'test.echo', static function (UiApiRequest $request): array { return ['body' => $request->jsonObject()]; }),
    new UiApiRoute('GET', '#^/items/(?P<id>[^/]+)$#', 'test.item', static function (UiApiRequest $request, UiApiRequestContext $context, array $parameters): array { unset($request, $context); return ['id' => $parameters['id']]; }, ['id' => 'positive_int']),
    new UiApiRoute('GET', '#^/boom$#', 'test.boom', static function (): array { throw new RuntimeException('SECRET_TOKEN at /private/server/path.php:99'); }),
    new UiApiRoute('GET', '#^/status/401$#', 'test.401', static function (): array { throw UiApiException::unauthorized(); }),
    new UiApiRoute('GET', '#^/status/403$#', 'test.403', static function (): array { throw UiApiException::forbidden(); }),
    new UiApiRoute('GET', '#^/status/409$#', 'test.409', static function (): array { throw UiApiException::conflict('The resource changed.', ['field' => 'version']); }),
    new UiApiRoute('GET', '#^/status/422$#', 'test.422', static function (): array { throw UiApiException::validation('The command is invalid.', ['field' => 'title']); }),
    new UiApiRoute('GET', '#^/status/429$#', 'test.429', static function (): array { throw UiApiException::rateLimited(15); }),
]);
$kernel = uiApiBuildKernel($routes);

wp04Test('GET / success envelope', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/')); wp04Same(200,$r->status()); $j=wp04Json($r); wp04Same('FreeITSM Browser UI API',$j['data']['name']); wp04Same('1',$j['meta']['apiVersion']); });
wp04Test('GET /health process-only', static function () use ($kernel): void { $j=wp04Json($kernel->handleServer(wp04Server('GET','/health'))); wp04Same('ok',$j['data']['status']); wp04Same('not_checked',$j['data']['checks']['database']); wp04Same('not_checked',$j['data']['checks']['session']); });
wp04Test('REQUEST_URI fallback without PATH_INFO', static function () use ($kernel): void { $s=wp04Server('GET','/health'); unset($s['PATH_INFO']); $s['REQUEST_URI']='/api/ui/v1/health'; wp04Same(200,$kernel->handleServer($s)->status()); });
wp04Test('HEAD uses GET and has no body', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('HEAD','/')); wp04Same(200,$r->status()); wp04Same('',$r->body()); });
wp04Test('OPTIONS has route Allow', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('OPTIONS','/health')); wp04Same(204,$r->status()); wp04Same('GET, HEAD, OPTIONS',$r->header('Allow')); });
wp04Test('unknown path is 404', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/missing')); wp04Same(404,$r->status()); wp04Same('not_found',wp04Json($r)['error']['code']); });
wp04Test('known path wrong method is 405', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('POST','/health')); wp04Same(405,$r->status()); wp04Same('GET, HEAD, OPTIONS',$r->header('Allow')); });
wp04Test('TRACE is not accepted', static function () use ($kernel): void { wp04Same(405,$kernel->handleServer(wp04Server('TRACE','/'))->status()); });
wp04Test('malformed JSON is 400', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('POST','/echo',['Content-Type'=>'application/json']),[],'{oops'); wp04Same(400,$r->status()); wp04Same('invalid_json',wp04Json($r)['error']['code']); });
wp04Test('top-level array is rejected', static function () use ($kernel): void { wp04Same(400,$kernel->handleServer(wp04Server('POST','/echo',['Content-Type'=>'application/json']),[],'[]')->status()); });
wp04Test('top-level scalar is rejected', static function () use ($kernel): void { wp04Same(400,$kernel->handleServer(wp04Server('POST','/echo',['Content-Type'=>'application/json']),[],'true')->status()); });
wp04Test('unsupported Content-Type is 415', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('POST','/echo',['Content-Type'=>'text/plain']),[],'{}'); wp04Same(415,$r->status()); wp04Same('unsupported_media_type',wp04Json($r)['error']['code']); });
wp04Test('JSON charset accepted', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('POST','/echo',['Content-Type'=>'application/json; charset=utf-8']),[],'{"name":"test"}'); wp04Same('test',wp04Json($r)['data']['body']['name']); });
wp04Test('vendor +json accepted', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('POST','/echo',['Content-Type'=>'application/vnd.freeitsm+json']),[],'{"accepted":true}'); wp04Same(true,wp04Json($r)['data']['body']['accepted']); });
wp04Test('empty optional command is empty object', static function () use ($kernel): void { wp04Same([],wp04Json($kernel->handleServer(wp04Server('POST','/echo'),[],''))['data']['body']); });
wp04Test('request ID propagated', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/',['X-Request-ID'=>'request-123'])); wp04Same('request-123',$r->header('X-Request-ID')); wp04Same('request-123',wp04Json($r)['meta']['requestId']); });
wp04Test('invalid request ID replaced', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/',['X-Request-ID'=>"bad\r\nid"])); wp04Assert($r->header('X-Request-ID')!=="bad\r\nid"); wp04Assert(preg_match('/^[A-Fa-f0-9-]{36}$/',(string)$r->header('X-Request-ID'))===1); });
wp04Test('correlation ID propagated', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/',['X-Request-ID'=>'r-1','X-Correlation-ID'=>'c-1'])); wp04Same('c-1',$r->header('X-Correlation-ID')); wp04Same('c-1',wp04Json($r)['meta']['correlationId']); });
wp04Test('correlation defaults to request ID', static function () use ($kernel): void { wp04Same('r-2',$kernel->handleServer(wp04Server('GET','/',['X-Request-ID'=>'r-2']))->header('X-Correlation-ID')); });
wp04Test('body/header IDs are identical', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/missing')); $j=wp04Json($r); wp04Same($r->header('X-Request-ID'),$j['meta']['requestId']); wp04Same($r->header('X-Correlation-ID'),$j['meta']['correlationId']); });
wp04Test('raw backslash rejected', static function () use ($kernel): void { wp04Same(400,$kernel->handleServer(wp04Server('GET','/bad\\path'))->status()); });
wp04Test('encoded slash rejected', static function () use ($kernel): void { wp04Same(400,$kernel->handleServer(wp04Server('GET','/bad%2fpath'))->status()); });
wp04Test('double-encoded backslash rejected', static function () use ($kernel): void { wp04Same(400,$kernel->handleServer(wp04Server('GET','/bad%255cpath'))->status()); });
wp04Test('dot segment rejected', static function () use ($kernel): void { wp04Same(400,$kernel->handleServer(wp04Server('GET','/../health'))->status()); });
wp04Test('invalid percent encoding rejected', static function () use ($kernel): void { wp04Same(400,$kernel->handleServer(wp04Server('GET','/bad%ZZ'))->status()); });
wp04Test('invalid typed parameter is 400', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/items/nope')); wp04Same(400,$r->status()); wp04Same('invalid_route_parameter',wp04Json($r)['error']['code']); });
wp04Test('valid typed parameter coerced', static function () use ($kernel): void { wp04Same(42,wp04Json($kernel->handleServer(wp04Server('GET','/items/42')))['data']['id']); });
wp04Test('internal exception hides secrets and path', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/boom')); $b=$r->body(); wp04Same(500,$r->status()); wp04Assert(strpos($b,'SECRET_TOKEN')===false); wp04Assert(strpos($b,'/private/server/path.php')===false); wp04Assert(strpos(strtolower($b),'trace')===false); });
wp04Test('401 semantics', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/status/401')); wp04Same(401,$r->status()); wp04Same('unauthenticated',wp04Json($r)['error']['code']); });
wp04Test('403 semantics', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/status/403')); wp04Same(403,$r->status()); wp04Same('forbidden',wp04Json($r)['error']['code']); });
wp04Test('409 semantics', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/status/409')); wp04Same(409,$r->status()); wp04Same('version',wp04Json($r)['error']['details']['field']); });
wp04Test('422 semantics', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/status/422')); wp04Same(422,$r->status()); wp04Same('validation_failed',wp04Json($r)['error']['code']); });
wp04Test('429 Retry-After', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/status/429')); wp04Same(429,$r->status()); wp04Same('15',$r->header('Retry-After')); });
wp04Test('no CORS grant', static function () use ($kernel): void { $r=$kernel->handleServer(wp04Server('GET','/')); wp04Same(null,$r->header('Access-Control-Allow-Origin')); wp04Same(null,$r->header('Access-Control-Allow-Credentials')); });
wp04Test('production PHP has no Session, DB config or machine auth call', static function (): void {
    $root=dirname(__DIR__); $source=''; foreach (array_merge(glob($root.'/*.php')?:[],glob($root.'/lib/*.php')?:[]) as $file) $source.=file_get_contents($file)?:'';
    $code=''; foreach (token_get_all($source) as $token) { if (is_array($token) && ($token[0]===T_COMMENT || $token[0]===T_DOC_COMMENT)) continue; $code.=is_array($token)?$token[1]:$token; }
    wp04Assert(preg_match('/\bsession_start\s*\(/i',$code)!==1,'Session start detected.');
    wp04Assert(stripos($code,'HTTP_AUTHORIZATION')===false,'Machine Authorization header detected.');
    wp04Assert(stripos($code,'Bearer ')===false,'Bearer API key detected.');
    wp04Assert(preg_match('/\b(?:require|include)(?:_once)?\b[^;]*config\.php/i',$code)!==1,'Database config include detected.');
});
wp04Test('OpenAPI declares required semantics', static function (): void { $c=json_decode((string)file_get_contents(dirname(__DIR__).'/openapi.json'),true); wp04Same('3.1.0',$c['openapi']??null); foreach(['400','401','403','404','405','409','415','422','429','500'] as $s) wp04Assert(isset($c['x-freeitsm-status-semantics'][$s]),'Missing '.$s); wp04Assert(isset($c['components']['schemas']['UiApiErrorEnvelope'])); });

$failures=0;
foreach($tests as $index=>$entry){ try{$entry[1](); echo 'PASS '.str_pad((string)($index+1),2,'0',STR_PAD_LEFT).' '.$entry[0].PHP_EOL;}catch(Throwable $error){$failures++; echo 'FAIL '.str_pad((string)($index+1),2,'0',STR_PAD_LEFT).' '.$entry[0].PHP_EOL.'     '.$error->getMessage().PHP_EOL;} }
echo sprintf('%d tests, %d failures',count($tests),$failures).PHP_EOL;
exit($failures===0?0:1);
