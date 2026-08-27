<?php

declare(strict_types=1);

final class UiApiRouter
{
    private array $routes;
    private UiApiSecurityRuntime $security;

    public function __construct(array $routes, UiApiSecurityRuntime $security)
    {
        foreach ($routes as $route) if (!$route instanceof UiApiRoute) throw new InvalidArgumentException('Every UI API route must be a UiApiRoute.');
        $this->routes = array_values($routes);
        $this->security = $security;
    }

    public function dispatch(UiApiRequest $request, UiApiRequestContext $context, UiApiResponseFactory $responses): UiApiHttpResponse
    {
        $matches = [];
        $allowed = [];
        foreach ($this->routes as $route) {
            $parameters = $route->match($request->path());
            if ($parameters === null) continue;
            $matches[] = [$route, $parameters];
            $allowed[] = $route->method();
            if ($route->method() === 'GET') $allowed[] = 'HEAD';
        }
        if ($matches === []) throw UiApiException::notFound();

        $allowed[] = 'OPTIONS';
        $allowed = $this->sortMethods(array_values(array_unique($allowed)));
        if ($request->method() === 'OPTIONS') {
            return new UiApiHttpResponse(204, [
                'Allow' => implode(', ', $allowed),
                'Cache-Control' => 'no-store',
                'X-Content-Type-Options' => 'nosniff',
                'X-FreeITSM-UI-Api-Version' => UiApiRequestContext::API_VERSION,
                'X-Request-ID' => $context->requestId(),
                'X-Correlation-ID' => $context->correlationId(),
            ], '');
        }

        $effectiveMethod = $request->method() === 'HEAD' ? 'GET' : $request->method();
        foreach ($matches as [$route, $parameters]) {
            if ($route->method() !== $effectiveMethod) continue;
            $authorizedContext = $this->security->authorize($route->security(), $request, $context, $parameters);
            $result = $route->invoke($request, $authorizedContext, $parameters);
            if ($result instanceof UiApiHttpResponse) $response = $result;
            elseif (is_array($result)) $response = $responses->success($result, $authorizedContext);
            else throw new RuntimeException('Route handler returned an unsupported response type.');
            return $request->method() === 'HEAD' ? $response->withoutBody() : $response;
        }
        throw UiApiException::methodNotAllowed($allowed);
    }

    private function sortMethods(array $methods): array
    {
        $order = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        usort($methods, static function (string $left, string $right) use ($order): int {
            $leftPosition = array_search($left, $order, true);
            $rightPosition = array_search($right, $order, true);
            return ($leftPosition === false ? 999 : $leftPosition) <=> ($rightPosition === false ? 999 : $rightPosition);
        });
        return $methods;
    }
}
