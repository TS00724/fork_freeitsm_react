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

    /** @return array<string,string> */
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

    public function missingSchema(PDOException $error): bool
    {
        $state = (string) $error->getCode();
        $driver = (int) ($error->errorInfo[1] ?? 0);
        return in_array($state, ['42S02', '42S22'], true) || in_array($driver, [1054, 1146], true);
    }

    private function loadConfiguration(): void
    {
        if (defined('DB_SERVER') && defined('DB_NAME') && defined('DB_USERNAME') && defined('DB_PASSWORD')) return;
        $config = dirname(__DIR__, 4) . '/config.php';
        if (!is_file($config)) throw new RuntimeException('FreeITSM database configuration is unavailable.');
        require_once $config;
    }
}
