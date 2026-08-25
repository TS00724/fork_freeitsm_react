<?php

declare(strict_types=1);

final class UiApiHttpResponse
{
    private int $status;
    private array $headers;
    private string $body;

    public function __construct(int $status, array $headers = [], string $body = '')
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp((string) $key, $name) === 0) {
                return (string) $value;
            }
        }
        return null;
    }

    public function withoutBody(): self
    {
        return new self($this->status, $this->headers, '');
    }

    public function send(): void
    {
        http_response_code($this->status);
        header_remove('Access-Control-Allow-Origin');
        header_remove('Access-Control-Allow-Credentials');

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, true);
        }

        if ($this->body !== '') {
            echo $this->body;
        }
    }
}
