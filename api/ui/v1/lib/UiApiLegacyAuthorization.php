<?php

declare(strict_types=1);

final class UiApiLegacyAuthorization
{
    private const MODULES = [
        'watchtower', 'tickets', 'assets', 'knowledge', 'changes', 'problems',
        'calendar', 'morning-checks', 'reporting', 'software', 'forms', 'contracts',
        'service-status', 'wiki', 'lms', 'process-mapper', 'tasks', 'cmdb',
        'network-mapper', 'workflow', 'war-room',
    ];

    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $root = dirname(__DIR__, 4);
        require_once $root . '/includes/db_errors.php';
        require_once $root . '/includes/capabilities.php';
    }

    public function capabilities(int $analystId, bool $isAdmin): array
    {
        if ($isAdmin) return array_values(capAll());
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
                if ($resolved !== null) $valid[] = $resolved;
            }
            return array_values(capExpandUmbrellas(array_values(array_unique($valid))));
        } catch (PDOException $error) {
            if (dbErrorIsMissingSchema($error)) return [];
            throw $error;
        }
    }

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
                $statement = $this->connection->prepare('SELECT module_key FROM team_modules WHERE team_id = ?');
                $statement->execute([(int) $team['id']]);
                $sources[] = ['all' => false, 'set' => $statement->fetchAll(PDO::FETCH_COLUMN)];
            }
            return $this->combine($sources, $this->permissionMode());
        } catch (PDOException $error) {
            if (dbErrorIsMissingSchema($error)) return self::MODULES;
            throw $error;
        }
    }

    private function analystModuleSource(int $analystId): array
    {
        $statement = $this->connection->prepare('SELECT can_access_all_modules FROM analysts WHERE id = ?');
        $statement->execute([$analystId]);
        if ((int) $statement->fetchColumn() === 1) return ['all' => true, 'set' => []];
        $statement = $this->connection->prepare('SELECT module_key FROM analyst_modules WHERE analyst_id = ?');
        $statement->execute([$analystId]);
        return ['all' => false, 'set' => $statement->fetchAll(PDO::FETCH_COLUMN)];
    }

    private function permissionMode(): string
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT setting_value FROM system_settings WHERE setting_key = 'module_permission_mode'"
            );
            $statement->execute();
            return $statement->fetchColumn() === 'least' ? 'least' : 'most';
        } catch (PDOException $error) {
            if (dbErrorIsMissingSchema($error)) return 'most';
            throw $error;
        }
    }

    private function combine(array $sources, string $mode): array
    {
        if ($mode === 'least') {
            $sets = [];
            foreach ($sources as $source) if (!$source['all']) $sets[] = $source['set'];
            if ($sets === []) return self::MODULES;
            $result = array_shift($sets);
            foreach ($sets as $set) $result = array_values(array_intersect($result, $set));
            return array_values(array_intersect(self::MODULES, array_unique($result)));
        }

        foreach ($sources as $source) if ($source['all']) return self::MODULES;
        $result = [];
        foreach ($sources as $source) $result = array_merge($result, $source['set']);
        return array_values(array_intersect(self::MODULES, array_unique($result)));
    }
}
