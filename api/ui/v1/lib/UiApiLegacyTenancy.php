<?php

declare(strict_types=1);

final class UiApiLegacyTenancy
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $root = dirname(__DIR__, 4);
        require_once $root . '/includes/db_errors.php';
        require_once $root . '/includes/tenancy.php';
    }

    /** @return array{tenant:array<string,mixed>,availableTenants:array<int,array<string,mixed>>,accessibleTenantIds:int[]} */
    public function resolve(int $analystId, UiApiSessionStore $session): array
    {
        if (!tenancyTablesReady($this->connection)) {
            return $this->singleTenantFallback($session);
        }

        if (!isMultiTenant($this->connection)) {
            $defaultId = getDefaultTenantId($this->connection);
            $tenant = getTenantById($this->connection, $defaultId) ?? [
                'id' => $defaultId,
                'name' => 'Default',
                'slug' => 'default',
            ];
            $normalized = $this->normalize($tenant);
            $session->set('active_tenant_id', $defaultId);
            return [
                'tenant' => $normalized,
                'availableTenants' => [$normalized],
                'accessibleTenantIds' => [$defaultId],
            ];
        }

        $accessibleIds = array_values(array_unique(array_map(
            'intval',
            getAccessibleTenantIds($this->connection, $analystId)
        )));
        $available = [];
        foreach (getAllTenants($this->connection, true) as $tenant) {
            if (in_array((int) $tenant['id'], $accessibleIds, true)) {
                $available[] = $this->normalize($tenant);
            }
        }
        if ($available === []) {
            throw UiApiException::forbidden('The authenticated actor has no active company access.');
        }

        $requested = (int) $session->get('active_tenant_id', 0);
        $active = $this->find($available, $requested);
        if ($active === null) {
            $active = $this->find($available, getDefaultTenantId($this->connection)) ?? $available[0];
            $session->set('active_tenant_id', (int) $active['id']);
        }

        return [
            'tenant' => $active,
            'availableTenants' => $available,
            'accessibleTenantIds' => array_map(
                static fn(array $tenant): int => (int) $tenant['id'],
                $available
            ),
        ];
    }

    private function singleTenantFallback(UiApiSessionStore $session): array
    {
        $tenant = [
            'id' => TENANCY_FALLBACK_TENANT_ID,
            'name' => 'Default',
            'slug' => 'default',
        ];
        $session->set('active_tenant_id', TENANCY_FALLBACK_TENANT_ID);
        return [
            'tenant' => $tenant,
            'availableTenants' => [$tenant],
            'accessibleTenantIds' => [TENANCY_FALLBACK_TENANT_ID],
        ];
    }

    private function normalize(array $tenant): array
    {
        return [
            'id' => (int) $tenant['id'],
            'name' => (string) $tenant['name'],
            'slug' => (string) $tenant['slug'],
        ];
    }

    private function find(array $tenants, int $tenantId): ?array
    {
        foreach ($tenants as $tenant) {
            if ((int) $tenant['id'] === $tenantId) return $tenant;
        }
        return null;
    }
}
