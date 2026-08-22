<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

final class UiRouter
{
    /** @var array<int,UiRoute> */
    private $routes;

    /** @param array<int,UiRoute> $routes */
    public function __construct(array $routes)
    {
        foreach ($routes as $route) {
            if (!$route instanceof UiRoute) {
                throw new \InvalidArgumentException('Router entries must be UiRoute instances.');
            }
        }
        $this->routes = array_values($routes);
    }

    /** @return array<int,UiRoute> */
    public function routes(): array
    {
        return $this->routes;
    }

    public function dispatch(UiRequest $request, UiRequestContext $context): UiRouteResult
    {
        $pathMatched = false;
        $allowed = [];
        $method = $request->method();

        foreach ($this->routes as $route) {
            $params = $route->matchPath($request->path());
            if ($params === null) {
                continue;
            }
            $pathMatched = true;
            $allowed[] = $route->method();
            if ($route->method() === 'GET') {
                $allowed[] = 'HEAD';
            }

            $matchesMethod = $route->method() === $method
                || ($method === 'HEAD' && $route->method() === 'GET');
            if ($matchesMethod) {
                return $route->invoke($request, $context, $params);
            }
        }

        $allowed = array_values(array_unique($allowed));
        sort($allowed);
        if ($pathMatched && $method === 'OPTIONS') {
            $allowed[] = 'OPTIONS';
            $allowed = array_values(array_unique($allowed));
            sort($allowed);
            return UiRouteResult::noContent(['Allow' => implode(', ', $allowed)]);
        }
        if ($pathMatched) {
            throw new UiApiException(
                405,
                'method_not_allowed',
                "Method {$method} is not allowed for {$request->path()}.",
                null,
                ['Allow' => implode(', ', $allowed)]
            );
        }

        throw new UiApiException(404, 'not_found', 'The requested UI API endpoint does not exist.');
    }
}
