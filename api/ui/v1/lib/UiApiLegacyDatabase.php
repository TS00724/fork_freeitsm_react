<?php

declare(strict_types=1);

final class UiApiLegacyDatabase
{
    private ?PDO $connection = null;

    public function connection(): PDO
    {
        if ($this->connection !== null) return $this->connection;
        $this->loadConfiguration();
        $dsn = 'mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $this->connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $this->connection;
    }

    public function actor(int $analystId): ?array
    {
        $statement = $this->connection()->prepare(
            'SELECT id, username, full_name, email, is_admin, auth_provider_id ' .
            'FROM analysts WHERE id = ? AND is_active = 1 LIMIT 1'
        );
        $statement->execute([$analystId]);
        $actor = $statement->fetch();
        if (!is_array($actor)) return null;
        $actor['id'] = (int) $actor['id'];
        $actor['is_admin'] = (bool) $actor['is_admin'];
        $actor['auth_source'] = $this->authSource((int) ($actor['auth_provider_id'] ?? 0));
        unset($actor['auth_provider_id']);
        return $actor;
    }

    public function preferences(int $analystId): array
    {
        try {
            $statement = $this->connection()->prepare(
                "SELECT preference_key, preference_value FROM user_preferences " .
                "WHERE analyst_id = ? AND preference_key IN ('interface_language', 'timezone')"
            );
            $statement->execute([$analystId]);
            $preferences = [];
            foreach ($statement->fetchAll() as $row) {
                $preferences[(string) $row['preference_key']] = (string) $row['preference_value'];
            }
            return $preferences;
        } catch (PDOException $error) {
            if ($this->missingSchema($error)) return [];
            throw $error;
        }
    }

    private function authSource(int $providerId): string
    {
        if ($providerId <= 0) return 'local';
        try {
            $statement = $this->connection()->prepare('SELECT protocol FROM auth_providers WHERE id = ? LIMIT 1');
            $statement->execute([$providerId]);
            $protocol = strtolower(trim((string) $statement->fetchColumn()));
            return in_array($protocol, ['ldap', 'oidc'], true) ? $protocol : 'external';
        } catch (PDOException $error) {
            if ($this->missingSchema($error)) return 'external';
            throw $error;
        }
    }

    private function loadConfiguration(): void
    {
        if (defined('DB_SERVER') && defined('DB_NAME') && defined('DB_USERNAME') && defined('DB_PASSWORD')) return;
        $config = dirname(__DIR__, 4) . '/config.php';
        if (!is_file($config)) throw new RuntimeException('FreeITSM database configuration is unavailable.');
        require_once $config;
    }

    private function missingSchema(PDOException $error): bool
    {
        $state = (string) $error->getCode();
        $driver = (int) ($error->errorInfo[1] ?? 0);
        return in_array($state, ['42S02', '42S22'], true) || in_array($driver, [1054, 1146], true);
    }
}
