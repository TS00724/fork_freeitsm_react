<?php

declare(strict_types=1);

final class UiApiKernel
{
    private UiApiRouter $router;
    private UiApiResponseFactory $responses;

    public function __construct(UiApiRouter $router)
    {
        $this->router = $router;
        $this->responses = new UiApiResponseFactory();
    }

    public function handle(UiApiRequest $request): UiApiHttpResponse
    {
        $context = UiApiRequestContext::fromRequest($request);
        try {
            return $this->router->dispatch($request, $context, $this->responses);
        } catch (UiApiException $error) {
            return $this->responses->error($error, $context);
        } catch (Throwable $error) {
            error_log(sprintf(
                'UI API unexpected error request_id=%s correlation_id=%s method=%s path=%s type=%s',
                $context->requestId(),
                $context->correlationId(),
                $request->method(),
                $request->path(),
                get_class($error)
            ));
            return $this->responses->serverError($context);
        }
    }

    public function handleServer(array $server, array $query = [], ?string $body = null): UiApiHttpResponse
    {
        try {
            return $this->handle(UiApiRequest::fromServer($server, $query, $body));
        } catch (UiApiException $error) {
            return $this->responses->error($error, UiApiRequestContext::fromServer($server));
        } catch (Throwable $error) {
            $context = UiApiRequestContext::fromServer($server);
            error_log(sprintf(
                'UI API request-construction error request_id=%s correlation_id=%s type=%s',
                $context->requestId(),
                $context->correlationId(),
                get_class($error)
            ));
            return $this->responses->serverError($context);
        }
    }
}
