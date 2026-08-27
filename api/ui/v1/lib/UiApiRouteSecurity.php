<?php

declare(strict_types=1);

final class UiApiRouteSecurity
{
    private bool $sessionRequired;
    private bool $optionalSession;
    private bool $csrfRequired;
    private bool $issueCsrf;
    private ?string $capability;
    private ?string $module;
    private ?string $tenantParameter;
    private $objectScope;
    private bool $hideObjectDenial;

    private function __construct(
        bool $sessionRequired,
        bool $optionalSession,
        bool $csrfRequired,
        bool $issueCsrf,
        ?string $capability = null,
        ?string $module = null,
        ?string $tenantParameter = null,
        ?callable $objectScope = null,
        bool $hideObjectDenial = true
    ) {
        $this->sessionRequired = $sessionRequired;
        $this->optionalSession = $optionalSession;
        $this->csrfRequired = $csrfRequired;
        $this->issueCsrf = $issueCsrf;
        $this->capability = $capability;
        $this->module = $module;
        $this->tenantParameter = $tenantParameter;
        $this->objectScope = $objectScope;
        $this->hideObjectDenial = $hideObjectDenial;
    }

    public static function publicRoute(): self { return new self(false, false, false, false); }
    public static function sessionProbe(): self { return new self(false, true, false, true); }
    public static function authenticated(bool $issueCsrf = false): self { return new self(true, false, false, $issueCsrf); }
    public static function authenticatedWrite(): self { return new self(true, false, true, true); }

    public function requiringCapability(string $capability): self
    {
        $copy = clone $this;
        $copy->capability = $capability;
        return $copy;
    }

    public function requiringModule(string $module): self
    {
        $copy = clone $this;
        $copy->module = $module;
        return $copy;
    }

    public function requiringTenantParameter(string $parameter): self
    {
        $copy = clone $this;
        $copy->tenantParameter = $parameter;
        return $copy;
    }

    public function requiringObjectScope(callable $scope, bool $hideDenial = true): self
    {
        $copy = clone $this;
        $copy->objectScope = $scope;
        $copy->hideObjectDenial = $hideDenial;
        return $copy;
    }

    public function usesSession(): bool { return $this->sessionRequired || $this->optionalSession; }
    public function sessionRequired(): bool { return $this->sessionRequired; }
    public function csrfRequired(): bool { return $this->csrfRequired; }
    public function issueCsrf(): bool { return $this->issueCsrf; }
    public function capability(): ?string { return $this->capability; }
    public function module(): ?string { return $this->module; }
    public function tenantParameter(): ?string { return $this->tenantParameter; }
    public function objectScope(): ?callable { return $this->objectScope; }
    public function hideObjectDenial(): bool { return $this->hideObjectDenial; }
}
