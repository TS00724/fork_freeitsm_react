<?php

declare(strict_types=1);

final class UiApiRoute
{
    private string $method;
    private string $pattern;
    private string $name;
    private $handler;
    private array $parameterTypes;
    public function __construct(string $method, string $pattern, string $name, callable $handler, array $parameterTypes = []) {
        $this->method = strtoupper($method); $this->pattern = $pattern; $this->name = $name; $this->handler = $handler; $this->parameterTypes = $parameterTypes;
    }
    public function method(): string { return $this->method; }
    public function match(string $path): ?array {
        $matched = preg_match($this->pattern, $path, $captures, PREG_UNMATCHED_AS_NULL);
        if ($matched === false) throw new RuntimeException('Invalid route pattern for ' . $this->name);
        if ($matched !== 1) return null;
        $parameters = [];
        foreach ($captures as $key => $value) if (is_string($key) && $value !== null) $parameters[$key] = $value;
        foreach ($this->parameterTypes as $name => $type) {
            if (!array_key_exists($name, $parameters)) throw UiApiException::badRequest('invalid_route_parameter', 'A required route parameter is missing.', ['parameter' => $name]);
            $parameters[$name] = $this->coerce($name, (string)$parameters[$name], (string)$type);
        }
        return $parameters;
    }
    public function invoke(UiApiRequest $request, UiApiRequestContext $context, array $parameters) { return call_user_func($this->handler, $request, $context, $parameters); }
    private function coerce(string $name, string $value, string $type) {
        if ($type === 'positive_int') {
            if ($value === '' || !ctype_digit($value) || (int)$value <= 0) throw UiApiException::badRequest('invalid_route_parameter', 'A route parameter is invalid.', ['parameter' => $name, 'expected' => 'positive integer']);
            return (int)$value;
        }
        if ($type === 'slug') {
            if (preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,127}$/', $value) !== 1) throw UiApiException::badRequest('invalid_route_parameter', 'A route parameter is invalid.', ['parameter' => $name, 'expected' => 'slug']);
            return $value;
        }
        if ($type === 'string') return $value;
        throw new LogicException('Unknown route parameter type: ' . $type);
    }
}

final class UiApiResponseFactory
{
    public function success($data, UiApiRequestContext $context, int $status = 200): UiApiHttpResponse {
        if ($status === 204) return new UiApiHttpResponse(204, $this->headers($context), '');
        return new UiApiHttpResponse($status, $this->headers($context), $this->encode(['data' => $data, 'meta' => $context->envelopeMeta()]));
    }
    public function error(UiApiException $exception, UiApiRequestContext $context): UiApiHttpResponse {
        $error = ['code' => $exception->errorCode(), 'message' => $exception->getMessage()];
        if ($exception->details() !== null) $error['details'] = $exception->details();
        return new UiApiHttpResponse($exception->httpStatus(), array_merge($this->headers($context), $exception->responseHeaders()), $this->encode(['error' => $error, 'meta' => $context->envelopeMeta()]));
    }
    public function serverError(UiApiRequestContext $context): UiApiHttpResponse { return $this->error(new UiApiException(500, 'server_error', 'An unexpected server error occurred.'), $context); }
    private function headers(UiApiRequestContext $context): array { return ['Content-Type' => 'application/json; charset=utf-8', 'Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff', 'X-FreeITSM-UI-Api-Version' => UiApiRequestContext::API_VERSION, 'X-Request-ID' => $context->requestId(), 'X-Correlation-ID' => $context->correlationId()]; }
    private function encode(array $payload): string {
        try { return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); }
        catch (JsonException $e) { error_log('UI API response encoding failure: ' . $e->getMessage()); return '{"error":{"code":"server_error","message":"An unexpected server error occurred."}}'; }
    }
}

final class UiApiRouter
{
    private array $routes;
    public function __construct(array $routes) {
        foreach ($routes as $route) if (!$route instanceof UiApiRoute) throw new InvalidArgumentException('Every UI API route must be a UiApiRoute.');
        $this->routes = array_values($routes);
    }
    public function dispatch(UiApiRequest $request, UiApiRequestContext $context, UiApiResponseFactory $responses): UiApiHttpResponse {
        $matches = []; $allowed = [];
        foreach ($this->routes as $route) {
            $parameters = $route->match($request->path());
            if ($parameters === null) continue;
            $matches[] = [$route, $parameters]; $allowed[] = $route->method(); if ($route->method() === 'GET') $allowed[] = 'HEAD';
        }
        if ($matches === []) throw UiApiException::notFound();
        $allowed[] = 'OPTIONS'; $allowed = $this->sortMethods(array_values(array_unique($allowed)));
        if ($request->method() === 'OPTIONS') return new UiApiHttpResponse(204, ['Allow' => implode(', ', $allowed), 'Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff', 'X-FreeITSM-UI-Api-Version' => UiApiRequestContext::API_VERSION, 'X-Request-ID' => $context->requestId(), 'X-Correlation-ID' => $context->correlationId()], '');
        $effective = $request->method() === 'HEAD' ? 'GET' : $request->method();
        foreach ($matches as $match) {
            $route = $match[0]; if ($route->method() !== $effective) continue;
            $result = $route->invoke($request, $context, $match[1]);
            if ($result instanceof UiApiHttpResponse) $response = $result;
            elseif (is_array($result)) $response = $responses->success($result, $context);
            else throw new RuntimeException('Route handler returned an unsupported response type.');
            return $request->method() === 'HEAD' ? $response->withoutBody() : $response;
        }
        throw UiApiException::methodNotAllowed($allowed);
    }
    private function sortMethods(array $methods): array {
        $order = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        usort($methods, static function (string $left, string $right) use ($order): int {
            $a = array_search($left, $order, true); $b = array_search($right, $order, true); return ($a === false ? 999 : $a) <=> ($b === false ? 999 : $b);
        });
        return $methods;
    }
}

final class UiApiKernel
{
    private UiApiRouter $router;
    private UiApiResponseFactory $responses;
    public function __construct(UiApiRouter $router) { $this->router = $router; $this->responses = new UiApiResponseFactory(); }
    public function handle(UiApiRequest $request): UiApiHttpResponse {
        $context = UiApiRequestContext::fromRequest($request);
        try { return $this->router->dispatch($request, $context, $this->responses); }
        catch (UiApiException $e) { return $this->responses->error($e, $context); }
        catch (Throwable $e) {
            error_log(sprintf('UI API unexpected error request_id=%s correlation_id=%s method=%s path=%s type=%s', $context->requestId(), $context->correlationId(), $request->method(), $request->path(), get_class($e)));
            return $this->responses->serverError($context);
        }
    }
    public function handleServer(array $server, array $query = [], ?string $body = null): UiApiHttpResponse {
        try { return $this->handle(UiApiRequest::fromServer($server, $query, $body)); }
        catch (UiApiException $e) { return $this->responses->error($e, UiApiRequestContext::fromServer($server)); }
        catch (Throwable $e) {
            $context = UiApiRequestContext::fromServer($server);
            error_log(sprintf('UI API request-construction error request_id=%s correlation_id=%s type=%s', $context->requestId(), $context->correlationId(), get_class($e)));
            return $this->responses->serverError($context);
        }
    }
}
