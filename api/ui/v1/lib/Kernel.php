<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

use Throwable;

final class UiApiKernel
{
    /** @var UiRouter */
    private $router;
    /** @var UiResponseFactory */
    private $responses;

    public function __construct(UiRouter $router, UiResponseFactory $responses)
    {
        $this->router = $router;
        $this->responses = $responses;
    }

    public function router(): UiRouter
    {
        return $this->router;
    }

    public function handleGlobals(): UiHttpResponse
    {
        try {
            return $this->handle(UiRequest::fromGlobals());
        } catch (UiApiException $e) {
            $fallback = UiRequest::fallbackFromGlobals();
            return $this->responses->fromException($e, UiRequestContext::fallback($fallback));
        } catch (Throwable $e) {
            $fallback = UiRequest::fallbackFromGlobals();
            $context = UiRequestContext::fallback($fallback);
            error_log('UI API v1 request construction failure [' . $context->requestId . ']: ' . $e->getMessage());
            return $this->responses->fromException(
                new UiApiException(500, 'server_error', 'An unexpected server error occurred.'),
                $context
            );
        }
    }

    public function handle(UiRequest $request): UiHttpResponse
    {
        try {
            $context = UiRequestContext::fromRequest($request);
        } catch (UiApiException $e) {
            return $this->responses->fromException($e, UiRequestContext::fallback($request));
        }

        try {
            return $this->responses->fromRouteResult($this->router->dispatch($request, $context), $context);
        } catch (UiApiException $e) {
            return $this->responses->fromException($e, $context);
        } catch (Throwable $e) {
            error_log(
                'UI API v1 handler failure [' . $context->requestId . '] '
                . $request->method() . ' ' . $request->path() . ': '
                . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine()
            );
            return $this->responses->fromException(
                new UiApiException(500, 'server_error', 'An unexpected server error occurred.'),
                $context
            );
        }
    }
}
