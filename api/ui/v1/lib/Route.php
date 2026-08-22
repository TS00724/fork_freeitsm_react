<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

use RuntimeException;

final class UiRoute
{
    /** @var string */
    private $method;
    /** @var string */
    private $template;
    /** @var string */
    private $name;
    /** @var callable */
    private $handler;
    /** @var array<int,string> */
    private $templateSegments;

    public function __construct(string $method, string $template, string $name, callable $handler)
    {
        $method = strtoupper(trim($method));
        if ($method === '' || !preg_match('/^[A-Z]+$/', $method)) {
            throw new RuntimeException('Route method is invalid.');
        }
        if (!preg_match('/^[A-Za-z][A-Za-z0-9._-]*$/', $name)) {
            throw new RuntimeException('Route name is invalid.');
        }

        $this->method = $method;
        $this->template = $template === '/' ? '/' : '/' . trim($template, '/');
        $this->name = $name;
        $this->handler = $handler;
        $trimmed = trim($this->template, '/');
        $this->templateSegments = $trimmed === '' ? [] : explode('/', $trimmed);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function template(): string
    {
        return $this->template;
    }

    public function name(): string
    {
        return $this->name;
    }

    /** @return array<string,mixed>|null null means the path shape did not match. */
    public function matchPath(string $path): ?array
    {
        $trimmed = trim($path, '/');
        $pathSegments = $trimmed === '' ? [] : explode('/', $trimmed);
        if (count($pathSegments) !== count($this->templateSegments)) {
            return null;
        }

        $params = [];
        foreach ($this->templateSegments as $index => $templateSegment) {
            $actual = $pathSegments[$index];
            if (!preg_match('/^\{([A-Za-z][A-Za-z0-9_]*)(?::(int|slug|string))?\}$/', $templateSegment, $match)) {
                if ($actual !== $templateSegment) {
                    return null;
                }
                continue;
            }

            $name = $match[1];
            $type = $match[2] ?? 'string';
            if ($type === 'int') {
                if (!preg_match('/^[1-9][0-9]*$/', $actual) || !self::fitsPositiveInteger($actual)) {
                    throw new UiApiException(
                        400,
                        'invalid_route_parameter',
                        "Route parameter '{$name}' must be a positive integer.",
                        ['parameter' => $name]
                    );
                }
                $params[$name] = (int)$actual;
                continue;
            }
            if ($type === 'slug' && !preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,127}$/', $actual)) {
                throw new UiApiException(
                    400,
                    'invalid_route_parameter',
                    "Route parameter '{$name}' is invalid.",
                    ['parameter' => $name]
                );
            }
            if ($type === 'string' && ($actual === '' || strlen($actual) > 256)) {
                throw new UiApiException(
                    400,
                    'invalid_route_parameter',
                    "Route parameter '{$name}' is invalid.",
                    ['parameter' => $name]
                );
            }
            $params[$name] = $actual;
        }

        return $params;
    }

    /** @param array<string,mixed> $params */
    public function invoke(UiRequest $request, UiRequestContext $context, array $params): UiRouteResult
    {
        $result = call_user_func($this->handler, $request, $context, $params);
        if (!$result instanceof UiRouteResult) {
            throw new RuntimeException("Route {$this->name} did not return UiRouteResult.");
        }
        return $result;
    }

    private static function fitsPositiveInteger(string $value): bool
    {
        $max = (string)PHP_INT_MAX;
        $length = strlen($value);
        $maxLength = strlen($max);
        return $length < $maxLength || ($length === $maxLength && strcmp($value, $max) <= 0);
    }
}
