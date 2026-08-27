<?php

declare(strict_types=1);

final class Wp05ArraySessionStore implements UiApiSessionStore
{
    private string $sessionId;
    private array $values;

    public function __construct(array $values = [], string $sessionId = 'test-session-1')
    {
        $this->values = $values;
        $this->sessionId = $sessionId;
    }

    public function start(): void {}
    public function id(): string { return $this->sessionId; }
    public function has(string $key): bool { return array_key_exists($key, $this->values); }
    public function get(string $key, $default = null) { return $this->has($key) ? $this->values[$key] : $default; }
    public function set(string $key, $value): void { $this->values[$key] = $value; }
    public function remove(string $key): void { unset($this->values[$key]); }
    public function changeId(string $sessionId): void { $this->sessionId = $sessionId; }
}

final class Wp05FakeSecurityProvider implements UiApiSecurityProvider
{
    public int $resolveCalls = 0;
    private array $states;

    public function __construct(array $states = []) { $this->states = $states; }

    public function resolve(int $analystId, UiApiSessionStore $session): ?array
    {
        $this->resolveCalls++;
        if (!isset($this->states[$analystId])) return null;
        $state = $this->states[$analystId];
        $requested = (int) $session->get('active_tenant_id', 0);
        if ($requested > 0) {
            foreach ($state['availableTenants'] ?? [] as $tenant) {
                if ((int) ($tenant['id'] ?? 0) === $requested) { $state['tenant'] = $tenant; break; }
            }
        }
        return $state;
    }

    public function switchTenant(int $analystId, int $tenantId, UiApiSessionStore $session): array
    {
        $state = $this->resolve($analystId, $session);
        if ($state === null) throw UiApiException::unauthorized();
        if (!in_array($tenantId, array_map('intval', $state['accessibleTenantIds'] ?? []), true)) throw UiApiException::forbidden('The authenticated actor cannot access that company.');
        $session->set('active_tenant_id', $tenantId);
        return $this->resolve($analystId, $session) ?? $state;
    }

    public function links(): array
    {
        return ['login' => '/login', 'logout' => '/logout', 'passwordChange' => '/auth/force_password_change.php'];
    }

    public static function state(array $overrides = []): array
    {
        $base = [
            'authenticated' => true,
            'actor' => ['id' => 7, 'username' => 'analyst', 'displayName' => 'Test Analyst', 'email' => 'analyst@example.test', 'isAdmin' => false, 'authSource' => 'oidc'],
            'tenant' => ['id' => 1, 'name' => 'Default', 'slug' => 'default'],
            'availableTenants' => [
                ['id' => 1, 'name' => 'Default', 'slug' => 'default'],
                ['id' => 2, 'name' => 'Example', 'slug' => 'example'],
            ],
            'accessibleTenantIds' => [1, 2],
            'capabilities' => ['tickets.manage'],
            'modules' => ['tickets'],
            'locale' => 'en',
            'timezone' => 'UTC',
            'links' => ['login' => '/login', 'logout' => '/logout', 'passwordChange' => '/auth/force_password_change.php'],
        ];
        return array_replace($base, $overrides);
    }
}

function wp05Runtime(Wp05ArraySessionStore $session, ?Wp05FakeSecurityProvider $provider = null): array
{
    $provider = $provider ?? new Wp05FakeSecurityProvider([7 => Wp05FakeSecurityProvider::state()]);
    return [new UiApiSecurityRuntime($session, $provider, new UiApiCsrfGuard()), $provider];
}
