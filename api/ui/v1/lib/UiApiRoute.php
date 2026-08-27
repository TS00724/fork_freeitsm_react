<?php

declare(strict_types=1);

final class UiApiRoute
{
    private string $method;
    private string $pattern;
    private string $name;
    private $handler;
    private array $parameterTypes;
    private UiApiRouteSecurity $security;

    public function __construct(
        string $method,
        string $pattern,
        string $name,
        callable $handler,
        array $parameterTypes = [],
        ?UiApiRouteSecurity $security = null
    ) {
        $this->method = strtoupper($method);
        $this->pattern = $pattern;
        $this->name = $name;
        $this->handler = $handler;
        $this->parameterTypes = $parameterTypes;
        $this->security = $security ?? UiApiRouteSecurity::publicRoute();
    }

    public function method(): string { return $this->method; }
    public function security(): UiApiRouteSecurity { return $this->security; }

    public function match(string $path): ?array
    {
        $matched = preg_match($this->pattern, $path, $captures, PREG_UNMATCHED_AS_NULL);
        if ($matched === false) throw new RuntimeException('Invalid route pattern for ' . $this->name);
        if ($matched !== 1) return null;

        $parameters = [];
        foreach ($captures as $key => $value) {
            if (is_string($key) && $value !== null) $parameters[$key] = $value;
        }
        foreach ($this->parameterTypes as $name => $type) {
            if (!array_key_exists($name, $parameters)) {
                throw UiApiException::badRequest(
                    'invalid_route_parameter',
                    'A required route parameter is missing.',
                    ['parameter' => $name]
                );
            }
            $parameters[$name] = $this->coerce($name, (string) $parameters[$name], (string) $type);
        }
        return $parameters;
    }

    public function invoke(UiApiRequest $request, UiApiRequestContext $context, array $parameters)
    {
        return call_user_func($this->handler, $request, $context, $parameters);
    }

    private function coerce(string $name, string $value, string $type)
    {
        if ($type === 'positive_int') {
            if ($value === '' || !ctype_digit($value) || (int) $value <= 0) {
                throw UiApiException::badRequest(
                    'invalid_route_parameter',
                    'A route parameter is invalid.',
                    ['parameter' => $name, 'expected' => 'positive integer']
                );
            }
            return (int) $value;
        }
        if ($type === 'slug') {
            if (preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,127}$/', $value) !== 1) {
                throw UiApiException::badRequest(
                    'invalid_route_parameter',
                    'A route parameter is invalid.',
                    ['parameter' => $name, 'expected' => 'slug']
                );
            }
            return $value;
        }
        if ($type === 'string') return $value;
        throw new LogicException('Unknown route parameter type: ' . $type);
    }
}
