<?php

declare(strict_types=1);

final class UiApiLegacySecurityProvider implements UiApiSecurityProvider
{
    private UiApiLegacyDatabase $database;
    private ?UiApiLegacyAuthorization $authorization = null;
    private ?UiApiLegacyTenancy $tenancy = null;

    public function __construct(?UiApiLegacyDatabase $database = null)
    {
        $this->database = $database ?? new UiApiLegacyDatabase();
    }

    public function resolve(int $analystId, UiApiSessionStore $session): ?array
    {
        $actorRow = $this->database->actor($analystId);
        if ($actorRow === null) return null;

        $tenantState = $this->tenancy()->resolve($analystId, $session);
        $preferences = $this->database->preferences($analystId);
        return [
            'authenticated' => true,
            'actor' => [
                'id' => (int) $actorRow['id'],
                'username' => (string) $actorRow['username'],
                'displayName' => (string) $actorRow['full_name'],
                'email' => (string) $actorRow['email'],
                'isAdmin' => (bool) $actorRow['is_admin'],
                'authSource' => (string) $actorRow['auth_source'],
            ],
            'tenant' => $tenantState['tenant'],
            'availableTenants' => $tenantState['availableTenants'],
            'accessibleTenantIds' => $tenantState['accessibleTenantIds'],
            'capabilities' => $this->authorization()->capabilities($analystId, (bool) $actorRow['is_admin']),
            'modules' => $this->authorization()->modules($analystId),
            'locale' => $this->locale($preferences['interface_language'] ?? 'en'),
            'timezone' => $this->timezone($preferences['timezone'] ?? date_default_timezone_get()),
            'links' => $this->links(),
        ];
    }

    public function switchTenant(int $analystId, int $tenantId, UiApiSessionStore $session): array
    {
        $state = $this->resolve($analystId, $session);
        if ($state === null) throw UiApiException::unauthorized('The authenticated Session is no longer valid.');
        if (!in_array($tenantId, array_map('intval', $state['accessibleTenantIds'] ?? []), true)) {
            throw UiApiException::forbidden('The authenticated actor cannot access that company.');
        }

        $session->set('active_tenant_id', $tenantId);
        $updated = $this->resolve($analystId, $session);
        if ($updated === null || (int) ($updated['tenant']['id'] ?? 0) !== $tenantId) {
            throw UiApiException::forbidden('The requested company is not active or accessible.');
        }
        return $updated;
    }

    public function links(): array
    {
        $base = defined('BASE_URL') ? '/' . trim((string) BASE_URL, '/') : '';
        if ($base === '/') $base = '';
        return [
            'login' => $base . '/login',
            'logout' => $base . '/logout',
            'passwordChange' => $base . '/auth/force_password_change.php',
        ];
    }

    private function authorization(): UiApiLegacyAuthorization
    {
        if ($this->authorization === null) {
            $this->authorization = new UiApiLegacyAuthorization($this->database->connection());
        }
        return $this->authorization;
    }

    private function tenancy(): UiApiLegacyTenancy
    {
        if ($this->tenancy === null) {
            $this->tenancy = new UiApiLegacyTenancy($this->database->connection());
        }
        return $this->tenancy;
    }

    private function locale(string $value): string
    {
        $value = trim($value);
        return preg_match('/^[A-Za-z]{2,3}(?:-[A-Za-z0-9]{2,8})?$/', $value) === 1 ? $value : 'en';
    }

    private function timezone(string $value): string
    {
        $value = trim($value);
        return in_array($value, timezone_identifiers_list(), true) ? $value : date_default_timezone_get();
    }
}
