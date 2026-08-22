<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

use RuntimeException;

final class UiApiException extends RuntimeException
{
    /** @var int */
    private $httpStatus;
    /** @var string */
    private $errorCode;
    /** @var array<string,mixed>|null */
    private $details;
    /** @var array<string,string> */
    private $responseHeaders;

    /**
     * @param array<string,mixed>|null $details
     * @param array<string,string> $responseHeaders
     */
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

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    /** @return array<string,mixed>|null */
    public function details(): ?array
    {
        return $this->details;
    }

    /** @return array<string,string> */
    public function responseHeaders(): array
    {
        return $this->responseHeaders;
    }
}
