<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

use JsonException;

final class UiRequest
{
    private const MAX_JSON_BYTES = 1048576;

    /** @var string */
    private $method;
    /** @var string */
    private $path;
    /** @var array<string,string> */
    private $headers;
    /** @var string */
    private $rawBody;
    /** @var array<string,mixed>|null */
    private $jsonBody;
    /** @var bool */
    private $jsonParsed = false;

    /** @param array<string,string> $headers Header names may use any case. */
    public function __construct(string $method, string $path, array $headers = [], string $rawBody = '')
    {
        $method = strtoupper(trim($method));
        if ($method === '' || !preg_match('/^[A-Z]+$/', $method)) {
            throw new UiApiException(400, 'bad_request', 'HTTP method is invalid.');
        }

        $normalisedHeaders = [];
        foreach ($headers as $name => $value) {
            $normalisedHeaders[strtolower(trim((string)$name))] = trim((string)$value);
        }

        $this->method = $method;
        $this->path = self::normalisePath($path);
        $this->headers = $normalisedHeaders;
        $this->rawBody = $rawBody;
        $this->jsonBody = null;
    }

    public static function fromGlobals(): self
    {
        $path = (string)($_SERVER['PATH_INFO'] ?? '');
        if ($path === '' && isset($_SERVER['ORIG_PATH_INFO'])) {
            $path = (string)$_SERVER['ORIG_PATH_INFO'];
        }
        if ($path === '' && isset($_GET['path'])) {
            $path = (string)$_GET['path'];
        }
        if ($path === '') {
            $path = '/';
        }

        $raw = file_get_contents('php://input');
        return new self(
            (string)($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path,
            self::headersFromServer($_SERVER),
            $raw === false ? '' : $raw
        );
    }

    /** Create a safe request solely so malformed-global errors can be enveloped. */
    public static function fallbackFromGlobals(): self
    {
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($method === '' || !preg_match('/^[A-Z]+$/', $method)) {
            $method = 'GET';
        }
        return new self($method, '/', self::headersFromServer($_SERVER), '');
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function header(string $name): ?string
    {
        $key = strtolower($name);
        return array_key_exists($key, $this->headers) ? $this->headers[$key] : null;
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }

    /**
     * Parse an object-shaped JSON body. Empty body becomes an empty object.
     *
     * @return array<string,mixed>
     */
    public function jsonBody(): array
    {
        if ($this->jsonParsed) {
            return $this->jsonBody ?? [];
        }
        $this->jsonParsed = true;

        if (trim($this->rawBody) === '') {
            $this->jsonBody = [];
            return [];
        }
        if (strlen($this->rawBody) > self::MAX_JSON_BYTES) {
            throw new UiApiException(413, 'payload_too_large', 'JSON request body exceeds 1 MiB.');
        }

        $contentTypeHeader = (string)($this->header('content-type') ?? '');
        $contentTypeParts = explode(';', $contentTypeHeader, 2);
        $contentType = strtolower(trim($contentTypeParts[0]));
        $isJson = $contentType === 'application/json'
            || (bool)preg_match('~^application/[a-z0-9!#$&^_.+-]+\+json$~', $contentType);
        if (!$isJson) {
            throw new UiApiException(
                415,
                'unsupported_media_type',
                'A non-empty request body must use application/json.'
            );
        }

        $trimmed = ltrim($this->rawBody);
        if ($trimmed === '' || $trimmed[0] !== '{') {
            throw new UiApiException(400, 'invalid_json', 'Request body must be a valid JSON object.');
        }

        try {
            $decoded = json_decode($this->rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UiApiException(400, 'invalid_json', 'Request body must be a valid JSON object.');
        }
        if (!is_array($decoded)) {
            throw new UiApiException(400, 'invalid_json', 'Request body must be a valid JSON object.');
        }

        $this->jsonBody = $decoded;
        return $decoded;
    }

    /** @param array<string,mixed> $server */
    private static function headersFromServer(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (strpos((string)$key, 'HTTP_') === 0) {
                $name = strtolower(str_replace('_', '-', substr((string)$key, 5)));
                $headers[$name] = (string)$value;
            }
        }
        if (isset($server['CONTENT_TYPE'])) {
            $headers['content-type'] = (string)$server['CONTENT_TYPE'];
        }
        if (isset($server['CONTENT_LENGTH'])) {
            $headers['content-length'] = (string)$server['CONTENT_LENGTH'];
        }
        return $headers;
    }

    private static function normalisePath(string $path): string
    {
        if (strlen($path) > 2048) {
            throw new UiApiException(400, 'bad_request', 'Request path is too long.');
        }
        if (strpos($path, '?') !== false || strpos($path, '#') !== false || strpos($path, '\\') !== false) {
            throw new UiApiException(400, 'bad_request', 'Request path is invalid.');
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $path)) {
            throw new UiApiException(400, 'bad_request', 'Request path is invalid.');
        }

        $trimmed = trim($path, '/');
        if ($trimmed === '') {
            return '/';
        }

        $parts = explode('/', $trimmed);
        $decodedParts = [];
        foreach ($parts as $part) {
            if ($part === '') {
                throw new UiApiException(400, 'bad_request', 'Request path contains an empty segment.');
            }
            $decoded = $part;
            for ($pass = 0; $pass < 3; $pass++) {
                if (preg_match('/%(?![0-9A-Fa-f]{2})/', $decoded)) {
                    throw new UiApiException(400, 'bad_request', 'Request path contains invalid percent encoding.');
                }
                $next = rawurldecode($decoded);
                if ($next === $decoded) {
                    break;
                }
                $decoded = $next;
            }
            if (preg_match('/%(?![0-9A-Fa-f]{2})/', $decoded)) {
                throw new UiApiException(400, 'bad_request', 'Request path contains invalid percent encoding.');
            }
            if ($decoded === '.' || $decoded === '..' || strpos($decoded, '/') !== false || strpos($decoded, '\\') !== false) {
                throw new UiApiException(400, 'bad_request', 'Request path contains a forbidden segment.');
            }
            if (preg_match('/[\x00-\x1F\x7F]/', $decoded)) {
                throw new UiApiException(400, 'bad_request', 'Request path contains a forbidden segment.');
            }
            $decodedParts[] = $decoded;
        }

        return '/' . implode('/', $decodedParts);
    }
}
