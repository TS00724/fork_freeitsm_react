<?php

declare(strict_types=1);

/**
 * Transport-level exception for the browser UI API.
 *
 * Business and authorization code added in later work packages may throw this
 * exception, but WP-04 itself does not authenticate a session or authorize a
 * tenant/capability.
 */
final class UiApiException extends RuntimeException
{
    private int $httpStatus;
    private string $errorCode;
    private ?array $details;
    private array $responseHeaders;

    public function __construct(
        int $httpStatus,
        string $errorCode,
        string $message,
        ?array $details = null,
        array $responseHeaders = []
    ) {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->errorCode = $errorCode;
        $this->details = $details;
        $this->responseHeaders = $responseHeaders;
    }

    public function httpStatus(): int { return $this->httpStatus; }
    public function errorCode(): string { return $this->errorCode; }
    public function details(): ?array { return $this->details; }
    public function responseHeaders(): array { return $this->responseHeaders; }

    public static function badRequest(string $code, string $message, ?array $details = null): self
    {
        return new self(400, $code, $message, $details);
    }

    public static function unauthorized(string $message = 'Authentication is required.'): self
    {
        return new self(401, 'unauthenticated', $message);
    }

    public static function forbidden(string $message = 'The authenticated actor is not permitted to perform this action.'): self
    {
        return new self(403, 'forbidden', $message);
    }

    public static function notFound(string $message = 'The requested UI API endpoint was not found.'): self
    {
        return new self(404, 'not_found', $message);
    }

    public static function methodNotAllowed(array $allowedMethods): self
    {
        $allow = implode(', ', array_values(array_unique($allowedMethods)));
        return new self(
            405,
            'method_not_allowed',
            'The HTTP method is not allowed for this endpoint.',
            null,
            ['Allow' => $allow]
        );
    }

    public static function conflict(string $message, ?array $details = null): self
    {
        return new self(409, 'conflict', $message, $details);
    }

    public static function unsupportedMediaType(string $message = 'Send a JSON object using application/json or application/*+json.'): self
    {
        return new self(415, 'unsupported_media_type', $message);
    }

    public static function validation(string $message, ?array $details = null): self
    {
        return new self(422, 'validation_failed', $message, $details);
    }

    public static function rateLimited(?int $retryAfterSeconds = null): self
    {
        $headers = [];
        if ($retryAfterSeconds !== null && $retryAfterSeconds >= 0) {
            $headers['Retry-After'] = (string)$retryAfterSeconds;
        }
        return new self(429, 'rate_limited', 'Too many requests.', null, $headers);
    }
}
