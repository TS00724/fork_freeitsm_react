<?php

declare(strict_types=1);

final class UiApiRequest
{
    private string $method;
    private string $path;
    private array $headers;
    private array $query;
    private string $rawBody;
    private string $serverOrigin;
    private bool $jsonParsed = false;
    private array $jsonObject = [];

    private function __construct(string $method, string $path, array $headers, array $query, string $body, string $serverOrigin)
    {
        $this->method = $method;
        $this->path = $path;
        $this->headers = $headers;
        $this->query = $query;
        $this->rawBody = $body;
        $this->serverOrigin = $serverOrigin;
    }

    public static function fromServer(array $server, array $query = [], ?string $body = null): self
    {
        $method = strtoupper(trim((string) ($server['REQUEST_METHOD'] ?? 'GET')));
        if ($method === '' || !preg_match("/^[A-Z][A-Z0-9!#$%&'*+.^_`|~-]*$/", $method)) {
            throw UiApiException::badRequest('invalid_method', 'The HTTP method is invalid.');
        }
        $path = self::resolvePath($server, $query);
        self::assertSafePath($path);
        if ($body === null) {
            $input = file_get_contents('php://input');
            $body = $input === false ? '' : $input;
        }
        return new self($method, $path, self::extractHeaders($server), $query, $body, UiApiServerOrigin::fromServer($server));
    }

    public function method(): string { return $this->method; }
    public function path(): string { return $this->path; }
    public function query(): array { return $this->query; }
    public function rawBody(): string { return $this->rawBody; }
    public function serverOrigin(): string { return $this->serverOrigin; }
    public function header(string $name): ?string { $key = strtolower($name); return array_key_exists($key, $this->headers) ? $this->headers[$key] : null; }

    public function jsonObject(): array
    {
        if ($this->jsonParsed) return $this->jsonObject;
        $this->jsonParsed = true;
        $trimmed = trim($this->rawBody);
        if ($trimmed === '') return $this->jsonObject = [];
        $contentType = strtolower(trim(explode(';', (string) ($this->header('content-type') ?? ''))[0]));
        if (preg_match('~^application/(?:[a-z0-9!#$&^_.\-]+\+)?json$~i', $contentType) !== 1) throw UiApiException::unsupportedMediaType();
        if ($trimmed[0] !== '{') throw UiApiException::badRequest('invalid_json', 'Request body must be a valid JSON object.');
        $decoded = json_decode($this->rawBody, true, 512, JSON_BIGINT_AS_STRING);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) throw UiApiException::badRequest('invalid_json', 'Request body must be a valid JSON object.');
        return $this->jsonObject = $decoded;
    }

    private static function resolvePath(array $server, array $query): string
    {
        $path = (string) ($server['PATH_INFO'] ?? '');
        if ($path === '') $path = (string) ($server['ORIG_PATH_INFO'] ?? '');
        if ($path === '' && isset($query['path'])) $path = (string) $query['path'];
        if ($path === '') {
            $requestPath = parse_url((string) ($server['REQUEST_URI'] ?? ''), PHP_URL_PATH);
            $scriptName = str_replace('\\', '/', (string) ($server['SCRIPT_NAME'] ?? ''));
            if (is_string($requestPath) && $requestPath !== '') {
                if ($scriptName !== '' && strpos($requestPath, $scriptName) === 0) $path = substr($requestPath, strlen($scriptName));
                elseif ($scriptName !== '') {
                    $directory = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
                    if ($directory !== '' && strpos($requestPath, $directory . '/') === 0) $path = substr($requestPath, strlen($directory));
                }
            }
        }
        return $path === '' ? '/' : '/' . ltrim($path, '/');
    }

    private static function extractHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) if (strpos((string) $key, 'HTTP_') === 0) $headers[strtolower(str_replace('_', '-', substr((string) $key, 5)))] = trim((string) $value);
        if (isset($server['CONTENT_TYPE'])) $headers['content-type'] = trim((string) $server['CONTENT_TYPE']);
        elseif (isset($server['HTTP_CONTENT_TYPE'])) $headers['content-type'] = trim((string) $server['HTTP_CONTENT_TYPE']);
        return $headers;
    }

    private static function assertSafePath(string $path): void
    {
        if ($path === '' || strlen($path) > 2048 || $path[0] !== '/') throw UiApiException::badRequest('invalid_path', 'The request path is invalid.');
        if (strpos($path, '\\') !== false || strpos($path, '?') !== false || strpos($path, '#') !== false || strpos($path, '//') === 0) throw UiApiException::badRequest('invalid_path', 'The request path contains a forbidden separator or delimiter.');
        if (self::containsControl($path)) throw UiApiException::badRequest('invalid_path', 'The request path contains a control character.');
        foreach (explode('/', $path) as $segment) {
            $decoded = $segment;
            for ($pass = 0; $pass < 3; $pass++) {
                if (preg_match('/%(?![0-9A-Fa-f]{2})/', $decoded)) throw UiApiException::badRequest('invalid_path', 'The request path contains invalid percent encoding.');
                $next = rawurldecode($decoded);
                if ($next === $decoded) break;
                $decoded = $next;
            }
            if ($decoded === '.' || $decoded === '..' || strpos($decoded, '/') !== false || strpos($decoded, '\\') !== false) throw UiApiException::badRequest('invalid_path', 'The request path contains an encoded separator or dot segment.');
            if (self::containsControl($decoded)) throw UiApiException::badRequest('invalid_path', 'The request path contains an encoded control character.');
        }
    }

    private static function containsControl(string $value): bool
    {
        for ($index = 0, $length = strlen($value); $index < $length; $index++) {
            $ord = ord($value[$index]);
            if ($ord <= 31 || $ord === 127) return true;
        }
        return false;
    }
}
