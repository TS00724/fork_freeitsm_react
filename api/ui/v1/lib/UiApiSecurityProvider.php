<?php

declare(strict_types=1);

interface UiApiSecurityProvider
{
    /** @return array<string,mixed>|null */
    public function resolve(int $analystId, UiApiSessionStore $session): ?array;

    /** @return array<string,mixed> */
    public function switchTenant(int $analystId, int $tenantId, UiApiSessionStore $session): array;

    /** @return array<string,string> */
    public function links(): array;
}
