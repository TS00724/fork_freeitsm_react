<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

use JsonException;

final class UiResponseFactory
{
    public const API_VERSION = '1';

    public function fromRouteResult(UiRouteResult $result, UiRequestContext $context): UiHttpResponse
    {
        if ($result->noContent || $result->status === 204) {
            return new UiHttpResponse(204, $this->headers($context, $result->headers, false), '');
        }

        $meta = array_merge($result->meta, $this->standardMeta($context));
        return $this->json(
            $result->status,
            ['data' => $result->data, 'meta' => $meta],
            $context,
            $result->headers
        );
    }

    public function fromException(UiApiException $exception, UiRequestContext $context): UiHttpResponse
    {
        $status = $exception->httpStatus();
        $code = $exception->errorCode();
        $message = $exception->getMessage();
        $details = $exception->details();

        if ($status >= 500) {
            $status = 500;
            $code = 'server_error';
            $message = 'An unexpected server error occurred.';
            $details = null;
        }

        $error = ['code' => $code, 'message' => $message];
        if ($details !== null) {
            $error['details'] = $details;
        }

        return $this->json(
            $status,
            ['error' => $error, 'meta' => $this->standardMeta($context)],
            $context,
            $exception->responseHeaders()
        );
    }

    /** @param array<string,mixed> $payload @param array<string,string> $extraHeaders */
    private function json(int $status, array $payload, UiRequestContext $context, array $extraHeaders): UiHttpResponse
    {
        try {
            $body = json_encode(
                $payload,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            $status = 500;
            $body = '{"error":{"code":"server_error","message":"An unexpected server error occurred."}}';
        }
        return new UiHttpResponse($status, $this->headers($context, $extraHeaders, true), $body);
    }

    /** @return array<string,mixed> */
    private function standardMeta(UiRequestContext $context): array
    {
        return [
            'apiVersion' => self::API_VERSION,
            'requestId' => $context->requestId,
            'correlationId' => $context->correlationId,
            'timestamp' => gmdate('Y-m-d\\TH:i:s\\Z'),
        ];
    }

    /**
     * @param array<string,string> $extra
     * @return array<string,string>
     */
    private function headers(UiRequestContext $context, array $extra, bool $json): array
    {
        $headers = [
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
            'X-FreeITSM-Ui-Api-Version' => self::API_VERSION,
            'X-Request-ID' => $context->requestId,
            'X-Correlation-ID' => $context->correlationId,
        ];
        if ($json) {
            $headers['Content-Type'] = 'application/json; charset=utf-8';
        }
        foreach ($extra as $name => $value) {
            $headers[$name] = $value;
        }
        return $headers;
    }
}
