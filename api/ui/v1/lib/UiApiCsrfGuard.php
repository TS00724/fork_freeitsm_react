<?php

declare(strict_types=1);

final class UiApiCsrfGuard
{
    public const HEADER_NAME = 'X-CSRF-Token';
    private const TOKEN_KEY = '_freeitsm_ui_csrf_token';
    private const SESSION_BINDING_KEY = '_freeitsm_ui_csrf_session';

    public function issue(UiApiSessionStore $session): string
    {
        $binding = $this->sessionBinding($session);
        $token = (string) $session->get(self::TOKEN_KEY, '');
        $storedBinding = (string) $session->get(self::SESSION_BINDING_KEY, '');
        if (!$this->validToken($token) || !hash_equals($binding, $storedBinding)) {
            $token = bin2hex(random_bytes(32));
            $session->set(self::TOKEN_KEY, $token);
            $session->set(self::SESSION_BINDING_KEY, $binding);
        }
        return $token;
    }

    public function rotate(UiApiSessionStore $session): string
    {
        $this->clear($session);
        return $this->issue($session);
    }

    public function clear(UiApiSessionStore $session): void
    {
        $session->remove(self::TOKEN_KEY);
        $session->remove(self::SESSION_BINDING_KEY);
    }

    public function validate(UiApiRequest $request, UiApiSessionStore $session): void
    {
        $this->validateOrigin($request);
        $presented = trim((string) ($request->header(self::HEADER_NAME) ?? ''));
        $expected = (string) $session->get(self::TOKEN_KEY, '');
        $binding = (string) $session->get(self::SESSION_BINDING_KEY, '');
        if (
            !$this->validToken($presented) ||
            !$this->validToken($expected) ||
            !hash_equals($this->sessionBinding($session), $binding) ||
            !hash_equals($expected, $presented)
        ) {
            throw UiApiException::csrfFailed('The CSRF token is missing, invalid or expired.');
        }
    }

    private function validateOrigin(UiApiRequest $request): void
    {
        $expected = $request->serverOrigin();
        $origin = trim((string) ($request->header('Origin') ?? ''));
        if ($origin !== '') {
            if ($origin === 'null' || !$this->sameOrigin($origin, $expected, false)) {
                throw UiApiException::csrfOriginFailed();
            }
            return;
        }

        $referer = trim((string) ($request->header('Referer') ?? ''));
        if ($referer === '' || !$this->sameOrigin($referer, $expected, true)) {
            throw UiApiException::csrfOriginFailed();
        }
    }

    private function sameOrigin(string $candidate, string $expected, bool $allowPath): bool
    {
        $parts = parse_url($candidate);
        if (
            !is_array($parts) ||
            empty($parts['scheme']) ||
            empty($parts['host']) ||
            isset($parts['user']) ||
            isset($parts['pass']) ||
            isset($parts['fragment'])
        ) {
            return false;
        }

        $scheme = strtolower((string) $parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) return false;
        if (!$allowPath && !empty($parts['path']) && $parts['path'] !== '/') return false;
        if (!$allowPath && isset($parts['query'])) return false;

        $origin = $scheme . '://' . strtolower((string) $parts['host']);
        if (isset($parts['port'])) {
            $port = (int) $parts['port'];
            $default = ($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80);
            if (!$default) $origin .= ':' . $port;
        }
        return hash_equals(strtolower($expected), $origin);
    }

    private function sessionBinding(UiApiSessionStore $session): string
    {
        return hash('sha256', $session->id());
    }

    private function validToken(string $token): bool
    {
        return preg_match('/^[a-f0-9]{64}$/', $token) === 1;
    }
}
