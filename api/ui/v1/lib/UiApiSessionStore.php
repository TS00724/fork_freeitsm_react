<?php

declare(strict_types=1);

interface UiApiSessionStore
{
    public function start(): void;
    public function id(): string;
    public function has(string $key): bool;
    public function get(string $key, $default = null);
    public function set(string $key, $value): void;
    public function remove(string $key): void;
}

final class UiApiNativeSessionStore implements UiApiSessionStore
{
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (headers_sent($file, $line)) {
                throw new RuntimeException("Unable to start the PHP Session after output at $file:$line.");
            }

            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_trans_sid', '0');
            session_set_cookie_params($this->hardenedCookieOptions());

            if (!session_start()) {
                throw new RuntimeException('Unable to start the PHP Session.');
            }
            return;
        }

        if (!sessionCookieParamsAreHardened() && !headers_sent()) {
            setcookie(session_name(), session_id(), $this->hardenedCookieOptions());
        }
    }

    public function id(): string { return session_id(); }
    public function has(string $key): bool { return array_key_exists($key, $_SESSION); }
    public function get(string $key, $default = null) { return $this->has($key) ? $_SESSION[$key] : $default; }
    public function set(string $key, $value): void { $_SESSION[$key] = $value; }
    public function remove(string $key): void { unset($_SESSION[$key]); }

    private function hardenedCookieOptions(): array
    {
        $current = session_get_cookie_params();
        $sameSite = strtolower((string) ($current['samesite'] ?? '')) === 'strict' ? 'Strict' : 'Lax';
        return [
            'lifetime' => 0,
            'path' => (string) (($current['path'] ?? '') !== '' ? $current['path'] : '/'),
            'domain' => (string) ($current['domain'] ?? ''),
            'secure' => !empty($current['secure']) || requestIsHttps(),
            'httponly' => true,
            'samesite' => $sameSite,
        ];
    }
}
