<?php

declare(strict_types=1);

final class UiApiRequestContext
{
    public const API_VERSION = '1';

    private string $requestId;
    private string $correlationId;
    private string $timestamp;
    private bool $authenticated = false;
    private ?array $actor = null;
    private ?array $tenant = null;
    private array $availableTenants = [];
    private array $capabilities = [];
    private array $modules = [];
    private array $accessibleTenantIds = [];
    private ?string $locale = null;
    private ?string $timezone = null;
    private ?string $csrfToken = null;
    private ?string $csrfHeader = null;
    private array $links = [];

    public function __construct(
        string $requestId,
        string $correlationId,
        ?string $timestamp = null,
        $actor = null,
        $tenant = null,
        $capabilities = null,
        ?string $locale = null,
        ?string $timezone = null
    ) {
        $this->requestId = $requestId;
        $this->correlationId = $correlationId;
        $this->timestamp = $timestamp ?? gmdate('Y-m-d\TH:i:s\Z');
        $this->actor = is_array($actor) ? $actor : null;
        $this->tenant = is_array($tenant) ? $tenant : null;
        $this->capabilities = is_array($capabilities) ? $capabilities : [];
        $this->locale = $locale;
        $this->timezone = $timezone;
        $this->authenticated = $this->actor !== null;
    }

    public static function fromRequest(UiApiRequest $request): self
    {
        return self::fromIds(
            $request->header('x-request-id'),
            $request->header('x-correlation-id')
        );
    }

    public static function fromServer(array $server): self
    {
        return self::fromIds(
            isset($server['HTTP_X_REQUEST_ID']) ? (string) $server['HTTP_X_REQUEST_ID'] : null,
            isset($server['HTTP_X_CORRELATION_ID']) ? (string) $server['HTTP_X_CORRELATION_ID'] : null
        );
    }

    /** @param array<string,mixed> $state */
    public function withSecurityState(array $state): self
    {
        $copy = clone $this;
        $copy->authenticated = !empty($state['authenticated']);
        $copy->actor = isset($state['actor']) && is_array($state['actor']) ? $state['actor'] : null;
        $copy->tenant = isset($state['tenant']) && is_array($state['tenant']) ? $state['tenant'] : null;
        $copy->availableTenants = isset($state['availableTenants']) && is_array($state['availableTenants'])
            ? array_values($state['availableTenants']) : [];
        $copy->capabilities = isset($state['capabilities']) && is_array($state['capabilities'])
            ? array_values($state['capabilities']) : [];
        $copy->modules = isset($state['modules']) && is_array($state['modules'])
            ? array_values($state['modules']) : [];
        $copy->accessibleTenantIds = isset($state['accessibleTenantIds']) && is_array($state['accessibleTenantIds'])
            ? array_values(array_map('intval', $state['accessibleTenantIds'])) : [];
        $copy->locale = isset($state['locale']) ? (string) $state['locale'] : null;
        $copy->timezone = isset($state['timezone']) ? (string) $state['timezone'] : null;
        $copy->csrfToken = isset($state['csrfToken']) ? (string) $state['csrfToken'] : null;
        $copy->csrfHeader = isset($state['csrfHeader']) ? (string) $state['csrfHeader'] : null;
        $copy->links = isset($state['links']) && is_array($state['links']) ? $state['links'] : [];
        return $copy;
    }

    public function requestId(): string { return $this->requestId; }
    public function correlationId(): string { return $this->correlationId; }
    public function isAuthenticated(): bool { return $this->authenticated; }
    public function actor(): ?array { return $this->actor; }
    public function tenant(): ?array { return $this->tenant; }
    public function availableTenants(): array { return $this->availableTenants; }
    public function capabilities(): array { return $this->capabilities; }
    public function modules(): array { return $this->modules; }
    public function accessibleTenantIds(): array { return $this->accessibleTenantIds; }
    public function locale(): ?string { return $this->locale; }
    public function timezone(): ?string { return $this->timezone; }
    public function csrfToken(): ?string { return $this->csrfToken; }
    public function csrfHeader(): ?string { return $this->csrfHeader; }
    public function links(): array { return $this->links; }

    public function envelopeMeta(): array
    {
        return [
            'apiVersion' => self::API_VERSION,
            'requestId' => $this->requestId,
            'correlationId' => $this->correlationId,
            'timestamp' => $this->timestamp,
        ];
    }

    public function unresolvedSecuritySlots(): array
    {
        if (!$this->authenticated) {
            return [
                'actor' => 'anonymous',
                'tenant' => 'anonymous',
                'capabilities' => 'anonymous',
                'locale' => 'unresolved',
                'timezone' => 'unresolved',
            ];
        }
        return [
            'actor' => $this->actor === null ? 'unresolved' : 'resolved',
            'tenant' => $this->tenant === null ? 'unresolved' : 'resolved',
            'capabilities' => 'resolved',
            'locale' => $this->locale === null ? 'unresolved' : 'resolved',
            'timezone' => $this->timezone === null ? 'unresolved' : 'resolved',
        ];
    }

    private static function fromIds(?string $request, ?string $correlation): self
    {
        $requestId = self::validId($request) ? trim((string) $request) : self::generateId();
        $correlationId = self::validId($correlation) ? trim((string) $correlation) : $requestId;
        return new self($requestId, $correlationId);
    }

    private static function validId(?string $value): bool
    {
        if ($value === null) return false;
        $value = trim($value);
        return $value !== '' &&
            strlen($value) <= 128 &&
            preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/', $value) === 1;
    }

    private static function generateId(): string
    {
        try {
            $bytes = random_bytes(16);
        } catch (Throwable $error) {
            $bytes = hash('sha256', uniqid('', true) . mt_rand(), true);
        }
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex(substr($bytes, 0, 16));
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' .
            substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
    }
}
