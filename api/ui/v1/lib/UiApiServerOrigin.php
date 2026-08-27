<?php

declare(strict_types=1);

final class UiApiServerOrigin
{
    public static function fromServer(array $server): string
    {
        $scheme = self::isHttps($server) ? 'https' : 'http';
        $host = trim((string) ($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? ''));
        if ($host === '' || strlen($host) > 255 || preg_match('/[\x00-\x20\x7f\\\/]/', $host) === 1) {
            throw UiApiException::badRequest('invalid_host', 'The request Host is invalid.');
        }

        $parsed = parse_url($scheme . '://' . $host);
        if (!is_array($parsed) || empty($parsed['host'])) {
            throw UiApiException::badRequest('invalid_host', 'The request Host is invalid.');
        }

        $origin = $scheme . '://' . strtolower((string) $parsed['host']);
        $port = isset($parsed['port']) ? (int) $parsed['port'] : null;
        if ($port !== null && !(($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80))) {
            $origin .= ':' . $port;
        }
        return $origin;
    }

    private static function isHttps(array $server): bool
    {
        if (!empty($server['HTTPS']) && strtolower((string) $server['HTTPS']) !== 'off') {
            return true;
        }
        if ((int) ($server['SERVER_PORT'] ?? 0) === 443) {
            return true;
        }
        if (defined('TRUST_PROXY_HTTPS') && TRUST_PROXY_HTTPS) {
            $forwarded = (string) ($server['HTTP_X_FORWARDED_PROTO'] ?? '');
            $first = strtolower(trim(explode(',', $forwarded)[0]));
            return $first === 'https';
        }
        return false;
    }
}
