<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

final class UiRouteResult
{
    /** @var mixed */
    public $data;
    /** @var int */
    public $status;
    /** @var array<string,mixed> */
    public $meta;
    /** @var array<string,string> */
    public $headers;
    /** @var bool */
    public $noContent;

    /**
     * @param mixed $data
     * @param array<string,mixed> $meta
     * @param array<string,string> $headers
     */
    public function __construct($data, int $status = 200, array $meta = [], array $headers = [], bool $noContent = false)
    {
        $this->data = $data;
        $this->status = $status;
        $this->meta = $meta;
        $this->headers = $headers;
        $this->noContent = $noContent;
    }

    /** @param mixed $data */
    public static function success($data, int $status = 200, array $meta = [], array $headers = []): self
    {
        return new self($data, $status, $meta, $headers, false);
    }

    /** @param array<string,string> $headers */
    public static function noContent(array $headers = []): self
    {
        return new self(null, 204, [], $headers, true);
    }
}
