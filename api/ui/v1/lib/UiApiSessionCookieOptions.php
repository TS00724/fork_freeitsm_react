<?php

declare(strict_types=1);

final class UiApiSessionCookieOptions
{
    /** @return array{lifetime:int,path:string,domain:string,secure:bool,httponly:bool,samesite:string} */
    public static function forSessionStart(): array
    {
        $current = session_get_cookie_params();
        return [
            'lifetime' => 0,
            'path' => self::path($current),
            'domain' => (string) ($current['domain'] ?? ''),
            'secure' => !empty($current['secure']) || requestIsHttps(),
            'httponly' => true,
            'samesite' => self::sameSite($current),
        ];
    }

    /** @return array{expires:int,path:string,domain:string,secure:bool,httponly:bool,samesite:string} */
    public static function forSetCookie(): array
    {
        $start = self::forSessionStart();
        return [
            'expires' => 0,
            'path' => $start['path'],
            'domain' => $start['domain'],
            'secure' => $start['secure'],
            'httponly' => $start['httponly'],
            'samesite' => $start['samesite'],
        ];
    }

    private static function path(array $current): string
    {
        return (string) (($current['path'] ?? '') !== '' ? $current['path'] : '/');
    }

    private static function sameSite(array $current): string
    {
        return strtolower((string) ($current['samesite'] ?? '')) === 'strict'
            ? 'Strict'
            : 'Lax';
    }
}
