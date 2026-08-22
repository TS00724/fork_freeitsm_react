<?php

declare(strict_types=1);

final class UiApiException extends RuntimeException
{
    private int $httpStatus;
    private string $errorCode;
    private ?array $details;
    private array $responseHeaders;

    public function __construct(int $status, string $code, string $message, ?array $details = null, array $headers = [])
    {
        parent::__construct($message);
        $this->httpStatus = $status;
        $this->errorCode = $code;
        $this->details = $details;
        $this->responseHeaders = $headers;
    }
    public function httpStatus(): int { return $this->httpStatus; }
    public function errorCode(): string { return $this->errorCode; }
    public function details(): ?array { return $this->details; }
    public function responseHeaders(): array { return $this->responseHeaders; }
    public static function badRequest(string $code, string $message, ?array $details = null): self { return new self(400, $code, $message, $details); }
    public static function unauthorized(string $message = 'Authentication is required.'): self { return new self(401, 'unauthenticated', $message); }
    public static function forbidden(string $message = 'The authenticated actor is not permitted to perform this action.'): self { return new self(403, 'forbidden', $message); }
    public static function notFound(): self { return new self(404, 'not_found', 'The requested UI API endpoint was not found.'); }
    public static function methodNotAllowed(array $allowed): self { return new self(405, 'method_not_allowed', 'The HTTP method is not allowed for this endpoint.', null, ['Allow' => implode(', ', $allowed)]); }
    public static function conflict(string $message, ?array $details = null): self { return new self(409, 'conflict', $message, $details); }
    public static function unsupportedMediaType(): self { return new self(415, 'unsupported_media_type', 'Send a JSON object using application/json or application/*+json.'); }
    public static function validation(string $message, ?array $details = null): self { return new self(422, 'validation_failed', $message, $details); }
    public static function rateLimited(?int $retryAfter = null): self { return new self(429, 'rate_limited', 'Too many requests.', null, $retryAfter === null ? [] : ['Retry-After' => (string)$retryAfter]); }
}

final class UiApiHttpResponse
{
    private int $status;
    private array $headers;
    private string $body;
    public function __construct(int $status, array $headers = [], string $body = '') { $this->status = $status; $this->headers = $headers; $this->body = $body; }
    public function status(): int { return $this->status; }
    public function headers(): array { return $this->headers; }
    public function body(): string { return $this->body; }
    public function header(string $name): ?string {
        foreach ($this->headers as $key => $value) if (strcasecmp((string)$key, $name) === 0) return (string)$value;
        return null;
    }
    public function withoutBody(): self { return new self($this->status, $this->headers, ''); }
    public function send(): void {
        http_response_code($this->status);
        header_remove('Access-Control-Allow-Origin');
        header_remove('Access-Control-Allow-Credentials');
        foreach ($this->headers as $name => $value) header($name . ': ' . $value, true);
        if ($this->body !== '') echo $this->body;
    }
}

final class UiApiRequest
{
    private string $method;
    private string $path;
    private array $headers;
    private array $query;
    private string $rawBody;
    private bool $jsonParsed = false;
    private array $jsonObject = [];
    private function __construct(string $method, string $path, array $headers, array $query, string $body) { $this->method = $method; $this->path = $path; $this->headers = $headers; $this->query = $query; $this->rawBody = $body; }
    public static function fromServer(array $server, array $query = [], ?string $body = null): self {
        $method = strtoupper(trim((string)($server['REQUEST_METHOD'] ?? 'GET')));
        if ($method === '' || !preg_match("/^[A-Z][A-Z0-9!#$%&'*+.^_`|~-]*$/", $method)) throw UiApiException::badRequest('invalid_method', 'The HTTP method is invalid.');
        $path = self::resolvePath($server, $query);
        self::assertSafePath($path);
        if ($body === null) { $input = file_get_contents('php://input'); $body = $input === false ? '' : $input; }
        return new self($method, $path, self::extractHeaders($server), $query, $body);
    }
    public function method(): string { return $this->method; }
    public function path(): string { return $this->path; }
    public function query(): array { return $this->query; }
    public function rawBody(): string { return $this->rawBody; }
    public function header(string $name): ?string { $key = strtolower($name); return array_key_exists($key, $this->headers) ? $this->headers[$key] : null; }
    public function jsonObject(): array {
        if ($this->jsonParsed) return $this->jsonObject;
        $this->jsonParsed = true;
        $trimmed = trim($this->rawBody);
        if ($trimmed === '') return $this->jsonObject = [];
        $contentType = strtolower(trim(explode(';', (string)($this->header('content-type') ?? ''))[0]));
        if (preg_match('~^application/(?:[a-z0-9!#$&^_.\-]+\+)?json$~i', $contentType) !== 1) throw UiApiException::unsupportedMediaType();
        if ($trimmed[0] !== '{') throw UiApiException::badRequest('invalid_json', 'Request body must be a valid JSON object.');
        $decoded = json_decode($this->rawBody, true, 512, JSON_BIGINT_AS_STRING);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) throw UiApiException::badRequest('invalid_json', 'Request body must be a valid JSON object.');
        return $this->jsonObject = $decoded;
    }
    private static function resolvePath(array $server, array $query): string {
        $path = (string)($server['PATH_INFO'] ?? '');
        if ($path === '') $path = (string)($server['ORIG_PATH_INFO'] ?? '');
        if ($path === '' && isset($query['path'])) $path = (string)$query['path'];
        if ($path === '') {
            $requestPath = parse_url((string)($server['REQUEST_URI'] ?? ''), PHP_URL_PATH);
            $scriptName = str_replace('\\', '/', (string)($server['SCRIPT_NAME'] ?? ''));
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
    private static function extractHeaders(array $server): array {
        $headers = [];
        foreach ($server as $key => $value) if (strpos((string)$key, 'HTTP_') === 0) $headers[strtolower(str_replace('_', '-', substr((string)$key, 5)))] = trim((string)$value);
        if (isset($server['CONTENT_TYPE'])) $headers['content-type'] = trim((string)$server['CONTENT_TYPE']);
        elseif (isset($server['HTTP_CONTENT_TYPE'])) $headers['content-type'] = trim((string)$server['HTTP_CONTENT_TYPE']);
        return $headers;
    }
    private static function assertSafePath(string $path): void {
        if ($path === '' || strlen($path) > 2048 || $path[0] !== '/') throw UiApiException::badRequest('invalid_path', 'The request path is invalid.');
        if (strpos($path, '\\') !== false || strpos($path, '?') !== false || strpos($path, '#') !== false || strpos($path, '//') === 0) throw UiApiException::badRequest('invalid_path', 'The request path contains a forbidden separator or delimiter.');
        if (self::containsControl($path)) throw UiApiException::badRequest('invalid_path', 'The request path contains a control character.');
        foreach (explode('/', $path) as $segment) {
            $decoded = $segment;
            for ($pass = 0; $pass < 3; $pass++) {
                if (preg_match('/%(?![0-9A-Fa-f]{2})/', $decoded)) throw UiApiException::badRequest('invalid_path', 'The request path contains invalid percent encoding.');
                $next = rawurldecode($decoded); if ($next === $decoded) break; $decoded = $next;
            }
            if ($decoded === '.' || $decoded === '..' || strpos($decoded, '/') !== false || strpos($decoded, '\\') !== false) throw UiApiException::badRequest('invalid_path', 'The request path contains an encoded separator or dot segment.');
            if (self::containsControl($decoded)) throw UiApiException::badRequest('invalid_path', 'The request path contains an encoded control character.');
        }
    }
    private static function containsControl(string $value): bool { for ($i = 0, $length = strlen($value); $i < $length; $i++) { $ord = ord($value[$i]); if ($ord <= 31 || $ord === 127) return true; } return false; }
}

final class UiApiRequestContext
{
    public const API_VERSION = '1';
    private string $requestId;
    private string $correlationId;
    private string $timestamp;
    private $actor;
    private $tenant;
    private $capabilities;
    private ?string $locale;
    private ?string $timezone;
    public function __construct(string $requestId, string $correlationId, ?string $timestamp = null, $actor = null, $tenant = null, $capabilities = null, ?string $locale = null, ?string $timezone = null) { $this->requestId = $requestId; $this->correlationId = $correlationId; $this->timestamp = $timestamp ?? gmdate('Y-m-d\TH:i:s\Z'); $this->actor = $actor; $this->tenant = $tenant; $this->capabilities = $capabilities; $this->locale = $locale; $this->timezone = $timezone; }
    public static function fromRequest(UiApiRequest $request): self { return self::fromIds($request->header('x-request-id'), $request->header('x-correlation-id')); }
    public static function fromServer(array $server): self { return self::fromIds(isset($server['HTTP_X_REQUEST_ID']) ? (string)$server['HTTP_X_REQUEST_ID'] : null, isset($server['HTTP_X_CORRELATION_ID']) ? (string)$server['HTTP_X_CORRELATION_ID'] : null); }
    public function requestId(): string { return $this->requestId; }
    public function correlationId(): string { return $this->correlationId; }
    public function envelopeMeta(): array { return ['apiVersion' => self::API_VERSION, 'requestId' => $this->requestId, 'correlationId' => $this->correlationId, 'timestamp' => $this->timestamp]; }
    public function unresolvedSecuritySlots(): array { return ['actor' => $this->actor === null ? 'unresolved' : 'resolved', 'tenant' => $this->tenant === null ? 'unresolved' : 'resolved', 'capabilities' => $this->capabilities === null ? 'unresolved' : 'resolved', 'locale' => $this->locale === null ? 'unresolved' : 'resolved', 'timezone' => $this->timezone === null ? 'unresolved' : 'resolved']; }
    private static function fromIds(?string $request, ?string $correlation): self { $requestId = self::validId($request) ? trim((string)$request) : self::generateId(); return new self($requestId, self::validId($correlation) ? trim((string)$correlation) : $requestId); }
    private static function validId(?string $value): bool { if ($value === null) return false; $value = trim($value); return $value !== '' && strlen($value) <= 128 && preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/', $value) === 1; }
    private static function generateId(): string { try { $bytes = random_bytes(16); } catch (Throwable $e) { $bytes = hash('sha256', uniqid('', true) . mt_rand(), true); } $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40); $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80); $hex = bin2hex(substr($bytes, 0, 16)); return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12); }
}
