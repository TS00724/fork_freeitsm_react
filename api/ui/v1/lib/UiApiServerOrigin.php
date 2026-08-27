<?php

declare(strict_types=1);

final class UiApiServerOrigin
{
    public static function fromServer(array $server): string
    {
        $scheme = self::isHttps($server) ? 'https' : 'http';
        $fromHostHeader = isset($server['HTTP_HOST']);
        $rawHost = trim((string) ($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? ''));
        if (
            $rawHost === '' ||
            strlen($rawHost) > 255 ||
            preg_match('/[\x00-\x20\x7f\\\/@?#]/', $rawHost) === 1
        ) {
            throw UiApiException::badRequest('invalid_host', 'The request Host is invalid.');
        }

        $parsed = parse_url($scheme . '://' . $rawHost);
        if (
            !is_array($parsed) ||
            empty($parsed['host']) ||
            isset($parsed['user']) ||
            isset($parsed['pass']) ||
            isset($parsed['query']) ||
            isset($parsed['fragment'])
        ) {
            throw UiApiException::badRequest('invalid_host', 'The request Host is invalid.');
        }

        $origin = $scheme . '://' . strtolower((string) $parsed['host']);
        $port = isset($parsed['port']) ? (int) $parsed['port'] : null;
        if ($port === null && !$fromHostHeader) {
            $serverPort = (int) ($server['SERVER_PORT'] ?? 0);
            if ($serverPort > 0) {
                $port = $serverPort;
            }
        }
        if ($port !== null && !(($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80))) {
            $origin .= ':' . $port;
        }
        return $origin;
    }

    private static function isHttps(array $server): bool
    {
        if (!empty($server['HTTPS']) && strtolower((string) $server['HTTPS']) !== 'off') return true;
        if ((int) ($server['SERVER_PORT'] ?? 0) === 443) return true;

        $forwarded = (string) ($server['HTTP_X_FORWARDED_PROTO'] ?? '');
        if ($forwarded === '') return false;
        self::loadTrustedProxyConfiguration();
        if (!defined('TRUST_PROXY_HTTPS') || !TRUST_PROXY_HTTPS) return false;
        return strtolower(trim(explode(',', $forwarded)[0])) === 'https';
    }

    private static function loadTrustedProxyConfiguration(): void
    {
        if (defined('TRUST_PROXY_HTTPS')) return;
        $config = dirname(__DIR__, 4) . '/config.php';
        if (is_file($config)) require_once $config;
    }
}
