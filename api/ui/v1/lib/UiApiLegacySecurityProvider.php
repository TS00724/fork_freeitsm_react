<?php

declare(strict_types=1);

final class UiApiLegacySecurityProvider implements UiApiSecurityProvider
{
    private UiApiLegacyDatabase $database;
    private ?UiApiLegacyAuthorization $authorization = null;

    public function __construct(?UiApiLegacyDatabase $database = null)
    {
        $this->database = $database ?? new UiApiLegacyDatabase();
    }

    public function resolve(int $analystId, UiApiSessionStore $session): ?array
    {
        $actorRow = $this->database->actor($analystId);
        if ($actorRow === null) {
            return null;
        }

        $authorization = $this->authorization();
        $tenantState = $authorization->tenants($analystId, $session);
        $preferences = $this->database->preferences($analystId);
        $locale = $this->locale($preferences['interface_language'] ?? 'en');
        $timezone = $this->timezone($preferences['timezone'] ?? date_default_timezone_get());

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
            'capabilities' => $authorization->capabilities($analystId, (bool) $actorRow['is_admin']),
            'modules' => $authorization->modules($analystId),
            'locale' => $locale,
            'timezone' => $timezone,
            'links' => $this->links(),
        ];
    }

    public function switchTenant(int $analystId, int $tenantId, UiApiSessionStore $session): array
    {
        $state = $this->resolve($analystId, $session);
        if ($state === null) {
            throw UiApiException::unauthorized('The authenticated Session is no longer valid.');
        }

        $allowedIds = array_map('intval', $state['accessibleTenantIds'] ?? []);
        if (!in_array($tenantId, $allowedIds, true)) {
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
        $base = defined('BASE_URL') ? (string) BASE_URL : '/';
        $base = '/' . trim($base, '/');
        if ($base === '/') {
            $base = '';
        }

        return [
            'login' => $base . '/login',
            'logout' => $base . '/logout',
            'passwordChange' => $base . '/force_password_change.php',
        ];
    }

    private function authorization(): UiApiLegacyAuthorization
    {
        if ($this->authorization === null) {
            $this->authorization = new UiApiLegacyAuthorization($this->database->connection());
        }
        return $this->authorization;
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
