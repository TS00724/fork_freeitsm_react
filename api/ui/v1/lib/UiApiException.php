<?php

declare(strict_types=1);

final class UiApiException extends RuntimeException
{
    private int $httpStatus;
    private string $errorCode;
    private ?array $details;
    private array $responseHeaders;

    public function __construct(
        int $status,
        string $code,
        string $message,
        ?array $details = null,
        array $headers = []
    ) {
        parent::__construct($message);
        $this->httpStatus = $status;
        $this->errorCode = $code;
        $this->details = $details;
        $this->responseHeaders = $headers;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function details(): ?array
    {
        return $this->details;
    }

    public function responseHeaders(): array
    {
        return $this->responseHeaders;
    }

    public static function badRequest(string $code, string $message, ?array $details = null): self
    {
        return new self(400, $code, $message, $details);
    }

    public static function unauthorized(string $message = 'Authentication is required.'): self
    {
        return new self(401, 'unauthenticated', $message);
    }

    public static function forbidden(
        string $message = 'The authenticated actor is not permitted to perform this action.'
    ): self {
        return new self(403, 'forbidden', $message);
    }

    public static function notFound(): self
    {
        return new self(404, 'not_found', 'The requested UI API endpoint was not found.');
    }

    public static function methodNotAllowed(array $allowed): self
    {
        return new self(
            405,
            'method_not_allowed',
            'The HTTP method is not allowed for this endpoint.',
            null,
            ['Allow' => implode(', ', $allowed)]
        );
    }

    public static function conflict(string $message, ?array $details = null): self
    {
        return new self(409, 'conflict', $message, $details);
    }

    public static function unsupportedMediaType(): self
    {
        return new self(
            415,
            'unsupported_media_type',
            'Send a JSON object using application/json or application/*+json.'
        );
    }

    public static function validation(string $message, ?array $details = null): self
    {
        return new self(422, 'validation_failed', $message, $details);
    }

    public static function rateLimited(?int $retryAfter = null): self
    {
        return new self(
            429,
            'rate_limited',
            'Too many requests.',
            null,
            $retryAfter === null ? [] : ['Retry-After' => (string) $retryAfter]
        );
    }
}
