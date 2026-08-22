<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

use Throwable;

final class UiRequestContext
{
    /** @var string */
    public $requestId;
    /** @var string */
    public $correlationId;
    /** @var string */
    public $method;
    /** @var string */
    public $path;

    // WP-05 fills these from the authoritative PHP session/security model.
    /** @var int|null */
    public $actorId = null;
    /** @var int|null */
    public $tenantId = null;
    /** @var int|null */
    public $companyId = null;
    /** @var array<int,string> */
    public $capabilities = [];
    /** @var string */
    public $locale = 'en';
    /** @var string */
    public $timezone = 'UTC';
    /** @var string */
    public $identitySource = 'unresolved';
    /** @var bool */
    public $authenticated = false;

    private function __construct(string $requestId, string $correlationId, string $method, string $path)
    {
        $this->requestId = $requestId;
        $this->correlationId = $correlationId;
        $this->method = $method;
        $this->path = $path;
    }

    public static function fromRequest(UiRequest $request): self
    {
        $requestId = $request->header('x-request-id');
        $correlationId = $request->header('x-correlation-id');

        if ($requestId !== null && !self::isValidIdentifier($requestId)) {
            throw new UiApiException(400, 'invalid_request_id', 'X-Request-ID is invalid.');
        }
        if ($correlationId !== null && !self::isValidIdentifier($correlationId)) {
            throw new UiApiException(400, 'invalid_request_id', 'X-Correlation-ID is invalid.');
        }

        $requestId = $requestId === null || $requestId === '' ? self::newIdentifier() : $requestId;
        $correlationId = $correlationId === null || $correlationId === '' ? $requestId : $correlationId;

        return new self($requestId, $correlationId, $request->method(), $request->path());
    }

    public static function fallback(UiRequest $request): self
    {
        $id = self::newIdentifier();
        return new self($id, $id, $request->method(), $request->path());
    }

    private static function isValidIdentifier(string $value): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{0,127}$/', $value);
    }

    private static function newIdentifier(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (Throwable $e) {
            return hash('sha256', uniqid('', true) . microtime(true));
        }
    }
}
