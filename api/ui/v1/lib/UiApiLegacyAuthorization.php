<?php

declare(strict_types=1);

final class UiApiLegacyAuthorization
{
    private const MODULES = [
        'watchtower', 'tickets', 'assets', 'knowledge', 'changes', 'problems',
        'calendar', 'morning-checks', 'reporting', 'software', 'forms',
        'contracts', 'service-status', 'wiki', 'lms', 'process-mapper', 'tasks',
        'cmdb', 'network-mapper', 'workflow', 'war-room',
    ];

    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $root = dirname(__DIR__, 4);
        require_once $root . '/includes/db_errors.php';
        require_once $root . '/includes/capabilities.php';
        require_once $root . '/includes/tenancy.php';
    }

    /** @return string[] */
    public function capabilities(int $analystId, bool $isAdmin): array
    {
        if ($isAdmin) {
            return array_values(capAll());
        }

        try {
            $statement = $this->connection->prepare(
                'SELECT DISTINCT rc.capability_key FROM rbac_role_capabilities rc ' .
                'JOIN rbac_roles r ON r.id = rc.role_id AND r.is_active = 1 ' .
                'WHERE rc.role_id IN (' .
                'SELECT role_id FROM rbac_analyst_roles WHERE analyst_id = ? ' .
                'UNION SELECT tr.role_id FROM rbac_team_roles tr ' .
                'JOIN analyst_teams at ON at.team_id = tr.team_id WHERE at.analyst_id = ?)'
            );
            $statement->execute([$analystId, $analystId]);
            $valid = [];
            foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $key) {
                $resolved = capFromKey((string) $key);
                if ($resolved !== null) {
                    $valid[] = $resolved;
                }
            }
            return array_values(capExpandUmbrellas(array_values(array_unique($valid))));
        } catch (PDOException $error) {
            if (dbErrorIsMissingSchema($error)) {
                return [];
            }
            throw $error;
        }
    }

    /** @return string[] */
    public function modules(int $analystId): array
    {
        try {
            $sources = [$this->analystModuleSource($analystId)];
            $teamStatement = $this->connection->prepare(
                'SELECT t.id, t.can_access_all_modules FROM analyst_teams at ' .
                'JOIN teams t ON t.id = at.team_id WHERE at.analyst_id = ?'
            );
            $teamStatement->execute([$analystId]);
            foreach ($teamStatement->fetchAll() as $team) {
                if ((int) $team['can_access_all_modules'] === 1) {
                    $sources[] = ['all' => true, 'set' => []];
                    continue;
                }
                $moduleStatement = $this->connection->prepare(
                    'SELECT module_key FROM team_modules WHERE team_id = ?'
                );
                $moduleStatement->execute([(int) $team['id']]);
                $sources[] = ['all' => false, 'set' => $moduleStatement->fetchAll(PDO::FETCH_COLUMN)];
            }

            return $this->combineModuleSources($sources, $this->modulePermissionMode());
        } catch (PDOException $error) {
            if (dbErrorIsMissingSchema($error)) {
                return self::MODULES;
            }
            throw $error;
        }
    }

    /** @return array{tenant:array<string,mixed>,availableTenants:array<int,array<string,mixed>>,accessibleTenantIds:int[]} */
    public function tenants(int $analystId, UiApiSessionStore $session): array
    {
        if (!tenancyTablesReady($this->connection)) {
            $fallback = [
                'id' => TENANCY_FALLBACK_TENANT_ID,
                'name' => 'Default',
                'slug' => 'default',
            ];
            return [
                'tenant' => $fallback,
                'availableTenants' => [$fallback],
                'accessibleTenantIds' => [TENANCY_FALLBACK_TENANT_ID],
            ];
        }

        $accessibleIds = array_values(array_unique(array_map(
            'intval',
            getAccessibleTenantIds($this->connection, $analystId)
        )));
        $available = array_values(array_map(static function (array $tenant): array {
            return [
                'id' => (int) $tenant['id'],
                'name' => (string) $tenant['name'],
                'slug' => (string) $tenant['slug'],
            ];
        }, array_filter(getAllTenants($this->connection, true), static function (array $tenant) use ($accessibleIds): bool {
            return in_array((int) $tenant['id'], $accessibleIds, true);
        })));

        if ($available === []) {
            throw UiApiException::forbidden('The authenticated actor has no active company access.');
        }

        $requested = (int) $session->get('active_tenant_id', 0);
        $active = $this->findTenant($available, $requested);
        if ($active === null) {
            $defaultId = getDefaultTenantId($this->connection);
            $active = $this->findTenant($available, $defaultId) ?? $available[0];
            $session->set('active_tenant_id', (int) $active['id']);
        }

        return [
            'tenant' => $active,
            'availableTenants' => $available,
            'accessibleTenantIds' => array_map(static fn(array $tenant): int => (int) $tenant['id'], $available),
        ];
    }

    /** @return array{all:bool,set:array<int,string>} */
    private function analystModuleSource(int $analystId): array
    {
        $statement = $this->connection->prepare(
            'SELECT can_access_all_modules FROM analysts WHERE id = ?'
        );
        $statement->execute([$analystId]);
        if ((int) $statement->fetchColumn() === 1) {
            return ['all' => true, 'set' => []];
        }

        $modules = $this->connection->prepare(
            'SELECT module_key FROM analyst_modules WHERE analyst_id = ?'
        );
        $modules->execute([$analystId]);
        return ['all' => false, 'set' => $modules->fetchAll(PDO::FETCH_COLUMN)];
    }

    private function modulePermissionMode(): string
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT setting_value FROM system_settings WHERE setting_key = 'module_permission_mode'"
            );
            $statement->execute();
            return $statement->fetchColumn() === 'least' ? 'least' : 'most';
        } catch (PDOException $error) {
            if (dbErrorIsMissingSchema($error)) {
                return 'most';
            }
            throw $error;
        }
    }

    /** @param array<int,array{all:bool,set:array<int,string>}> $sources */
    private function combineModuleSources(array $sources, string $mode): array
    {
        if ($mode === 'least') {
            $sets = array_values(array_map(static fn(array $source): array => $source['set'], array_filter(
                $sources,
                static fn(array $source): bool => !$source['all']
            )));
            if ($sets === []) {
                return self::MODULES;
            }
            $result = array_shift($sets);
            foreach ($sets as $set) {
                $result = array_values(array_intersect($result, $set));
            }
            return array_values(array_intersect(self::MODULES, array_unique($result)));
        }

        foreach ($sources as $source) {
            if ($source['all']) {
                return self::MODULES;
            }
        }
        $result = [];
        foreach ($sources as $source) {
            $result = array_merge($result, $source['set']);
        }
        return array_values(array_intersect(self::MODULES, array_unique($result)));
    }

    private function findTenant(array $tenants, int $tenantId): ?array
    {
        foreach ($tenants as $tenant) {
            if ((int) $tenant['id'] === $tenantId) {
                return $tenant;
            }
        }
        return null;
    }
}
