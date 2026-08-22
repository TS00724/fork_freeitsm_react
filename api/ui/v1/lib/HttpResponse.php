<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

final class UiHttpResponse
{
    /** @var int */
    private $status;
    /** @var array<string,string> */
    private $headers;
    /** @var string */
    private $body;

    /** @param array<string,string> $headers */
    public function __construct(int $status, array $headers, string $body)
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function status(): int
    {
        return $this->status;
    }

    /** @return array<string,string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function emit(bool $suppressBody = false): void
    {
        header_remove('X-Powered-By');
        header_remove('Content-Type');
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        if (!$suppressBody && $this->status !== 204) {
            echo $this->body;
        }
    }
}
