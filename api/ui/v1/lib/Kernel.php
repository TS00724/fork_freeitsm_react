<?php

declare(strict_types=1);

/** Executes one UI API request and contains all transport-level failures. */
final class UiApiKernel
{
    private UiApiRouter $router;
    private UiApiResponseFactory $responses;

    public function __construct(UiApiRouter $router, ?UiApiResponseFactory $responses = null)
    {
        $this->router = $router;
        $this->responses = $responses ?? new UiApiResponseFactory();
    }

    public function handle(UiApiRequest $request): UiApiHttpResponse
    {
        $context = UiApiRequestContext::fromRequest($request);
        try {
            return $this->router->dispatch($request, $context, $this->responses);
        } catch (UiApiException $e) {
            return $this->responses->error($e, $context);
        } catch (Throwable $e) {
            $this->logUnexpected($e, $request, $context);
            return $this->responses->serverError($context);
        }
    }

    /**
     * Create and handle a request while also containing errors thrown during
     * path/header/body construction. Tests can supply all inputs explicitly.
     */
    public function handleServer(array $server, array $query = [], ?string $rawBody = null): UiApiHttpResponse
    {
        try {
            return $this->handle(UiApiRequest::fromServer($server, $query, $rawBody));
        } catch (UiApiException $e) {
            return $this->responses->error($e, UiApiRequestContext::fromServer($server));
        } catch (Throwable $e) {
            $context = UiApiRequestContext::fromServer($server);
            error_log(sprintf(
                'UI API unhandled request-construction error request_id=%s correlation_id=%s type=%s',
                $context->requestId(),
                $context->correlationId(),
                get_class($e)
            ));
            return $this->responses->serverError($context);
        }
    }

    private function logUnexpected(
        Throwable $error,
        UiApiRequest $request,
        UiApiRequestContext $context
    ): void {
        // Never log request bodies, cookies, session IDs or authorization data.
        error_log(sprintf(
            'UI API unexpected error request_id=%s correlation_id=%s method=%s path=%s type=%s',
            $context->requestId(),
            $context->correlationId(),
            $request->method(),
            $request->path(),
            get_class($error)
        ));
    }
}
