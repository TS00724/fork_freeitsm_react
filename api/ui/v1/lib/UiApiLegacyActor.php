<?php

declare(strict_types=1);

final class UiApiLegacyActor
{
    private UiApiLegacyDatabase $database;

    public function __construct(UiApiLegacyDatabase $database)
    {
        $this->database = $database;
    }

    /** @return array<string,mixed>|null */
    public function resolve(int $analystId): ?array
    {
        $connection = $this->database->connection();
        $statement = $connection->prepare(
            'SELECT id, username, full_name, email FROM analysts ' .
            'WHERE id = ? AND is_active = 1 LIMIT 1'
        );
        $statement->execute([$analystId]);
        $actor = $statement->fetch();
        if (!is_array($actor)) return null;

        $actor['id'] = (int) $actor['id'];
        $actor['is_admin'] = $this->optionalInteger($connection, $analystId, 'is_admin') === 1;
        $providerId = $this->optionalInteger($connection, $analystId, 'auth_provider_id');
        $actor['auth_source'] = $this->authSource($connection, $providerId);
        return $actor;
    }

    private function optionalInteger(PDO $connection, int $analystId, string $column): int
    {
        if (preg_match('/^[a-z_]+$/', $column) !== 1) {
            throw new LogicException('Unsafe analyst column name.');
        }
        try {
            $statement = $connection->prepare("SELECT `$column` FROM analysts WHERE id = ? LIMIT 1");
            $statement->execute([$analystId]);
            return (int) $statement->fetchColumn();
        } catch (PDOException $error) {
            if ($this->database->missingSchema($error)) return 0;
            throw $error;
        }
    }

    private function authSource(PDO $connection, int $providerId): string
    {
        if ($providerId <= 0) return 'local';
        foreach (['protocol', 'provider_type', 'type'] as $column) {
            try {
                $statement = $connection->prepare("SELECT `$column` FROM auth_providers WHERE id = ? LIMIT 1");
                $statement->execute([$providerId]);
                $value = strtolower(trim((string) $statement->fetchColumn()));
                if ($value !== '') return in_array($value, ['ldap', 'oidc'], true) ? $value : 'external';
            } catch (PDOException $error) {
                if (!$this->database->missingSchema($error)) throw $error;
            }
        }
        return 'external';
    }
}
