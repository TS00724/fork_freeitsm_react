<?php

declare(strict_types=1);

/** Method-aware dispatcher with explicit 404, 405, HEAD and OPTIONS behavior. */
final class UiApiRouter
{
    /** @var UiApiRoute[] */
    private array $routes;

    public function __construct(array $routes)
    {
        foreach ($routes as $route) {
            if (!$route instanceof UiApiRoute) {
                throw new InvalidArgumentException('Every UI API route must be a UiApiRoute.');
            }
        }
        $this->routes = array_values($routes);
    }

    public function dispatch(
        UiApiRequest $request,
        UiApiRequestContext $context,
        UiApiResponseFactory $responses
    ): UiApiHttpResponse {
        $pathMatches = [];
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $parameters = $route->match($request->path());
            if ($parameters === null) {
                continue;
            }
            $pathMatches[] = [$route, $parameters];
            $allowedMethods[] = $route->method();
            if ($route->method() === 'GET') {
                $allowedMethods[] = 'HEAD';
            }
        }

        if ($pathMatches === []) {
            throw UiApiException::notFound();
        }

        $allowedMethods[] = 'OPTIONS';
        $allowedMethods = $this->sortMethods(array_values(array_unique($allowedMethods)));

        if ($request->method() === 'OPTIONS') {
            return new UiApiHttpResponse(
                204,
                [
                    'Allow' => implode(', ', $allowedMethods),
                    'Cache-Control' => 'no-store',
                    'X-Content-Type-Options' => 'nosniff',
                    'X-FreeITSM-UI-Api-Version' => UiApiRequestContext::API_VERSION,
                    'X-Request-ID' => $context->requestId(),
                    'X-Correlation-ID' => $context->correlationId(),
                ],
                ''
            );
        }

        $effectiveMethod = $request->method() === 'HEAD' ? 'GET' : $request->method();
        foreach ($pathMatches as $match) {
            /** @var UiApiRoute $route */
            $route = $match[0];
            if ($route->method() !== $effectiveMethod) {
                continue;
            }

            $result = $route->invoke($request, $context, $match[1]);
            if ($result instanceof UiApiHttpResponse) {
                $response = $result;
            } elseif (is_array($result)) {
                $response = $responses->success($result, $context);
            } else {
                throw new RuntimeException('Route handler returned an unsupported response type.');
            }

            return $request->method() === 'HEAD' ? $response->withoutBody() : $response;
        }

        throw UiApiException::methodNotAllowed($allowedMethods);
    }

    private function sortMethods(array $methods): array
    {
        $order = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        usort($methods, static function (string $left, string $right) use ($order): int {
            $leftIndex = array_search($left, $order, true);
            $rightIndex = array_search($right, $order, true);
            $leftIndex = $leftIndex === false ? 999 : $leftIndex;
            $rightIndex = $rightIndex === false ? 999 : $rightIndex;
            return $leftIndex <=> $rightIndex;
        });
        return $methods;
    }
}
