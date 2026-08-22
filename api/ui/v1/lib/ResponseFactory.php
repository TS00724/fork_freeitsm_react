<?php

declare(strict_types=1);

/** Builds the single success/error envelope used by the browser UI API. */
final class UiApiResponseFactory
{
    public function success(
        $data,
        UiApiRequestContext $context,
        int $status = 200,
        ?array $additionalMeta = null
    ): UiApiHttpResponse {
        if ($status === 204) {
            return new UiApiHttpResponse(204, $this->headers($context), '');
        }

        $meta = $additionalMeta === null
            ? $context->envelopeMeta()
            : array_merge($additionalMeta, $context->envelopeMeta());

        return new UiApiHttpResponse(
            $status,
            $this->headers($context),
            $this->encode(['data' => $data, 'meta' => $meta])
        );
    }

    public function error(UiApiException $exception, UiApiRequestContext $context): UiApiHttpResponse
    {
        $error = [
            'code' => $exception->errorCode(),
            'message' => $exception->getMessage(),
        ];
        if ($exception->details() !== null) {
            $error['details'] = $exception->details();
        }

        return new UiApiHttpResponse(
            $exception->httpStatus(),
            array_merge($this->headers($context), $exception->responseHeaders()),
            $this->encode(['error' => $error, 'meta' => $context->envelopeMeta()])
        );
    }

    public function serverError(UiApiRequestContext $context): UiApiHttpResponse
    {
        return $this->error(
            new UiApiException(500, 'server_error', 'An unexpected server error occurred.'),
            $context
        );
    }

    private function headers(UiApiRequestContext $context): array
    {
        return [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
            'X-FreeITSM-UI-Api-Version' => UiApiRequestContext::API_VERSION,
            'X-Request-ID' => $context->requestId(),
            'X-Correlation-ID' => $context->correlationId(),
        ];
    }

    private function encode(array $payload): string
    {
        try {
            return json_encode(
                $payload,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            error_log('UI API response encoding failure: ' . $e->getMessage());
            return '{"error":{"code":"server_error","message":"An unexpected server error occurred."}}';
        }
    }
}
