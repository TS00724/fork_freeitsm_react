<?php

declare(strict_types=1);

function wp05Kernel(
    Wp05ArraySessionStore $session,
    ?Wp05FakeSecurityProvider $provider = null
): array {
    [$runtime, $provider] = wp05Runtime($session, $provider);
    $routes = array_merge(uiApiRoutes($runtime), [
        new UiApiRoute(
            'GET', '#^/protected$#', 'test.protected',
            static fn(): array => ['ok' => true],
            [], UiApiRouteSecurity::authenticated()
        ),
        new UiApiRoute(
            'POST', '#^/write$#', 'test.write',
            static fn(): array => ['ok' => true],
            [], UiApiRouteSecurity::authenticatedWrite()
        ),
        new UiApiRoute(
            'GET', '#^/capability$#', 'test.capability',
            static fn(): array => ['ok' => true],
            [], UiApiRouteSecurity::authenticated()->requiringCapability('tickets.manage')
        ),
        new UiApiRoute(
            'GET', '#^/module$#', 'test.module',
            static fn(): array => ['ok' => true],
            [], UiApiRouteSecurity::authenticated()->requiringModule('tickets')
        ),
        new UiApiRoute(
            'GET', '#^/tenants/(?P<tenantId>[^/]+)$#', 'test.tenant',
            static fn(): array => ['ok' => true],
            ['tenantId' => 'positive_int'],
            UiApiRouteSecurity::authenticated()->requiringTenantParameter('tenantId')
        ),
        new UiApiRoute(
            'GET', '#^/objects/(?P<id>[^/]+)$#', 'test.object',
            static fn(): array => ['ok' => true],
            ['id' => 'positive_int'],
            UiApiRouteSecurity::authenticated()->requiringObjectScope(
                static fn(array $state, array $parameters): bool => (int) $parameters['id'] === 1
            )
        ),
    ]);
    return [uiApiBuildKernel($routes, $runtime), $provider];
}

function wp05Csrf(UiApiKernel $kernel): string
{
    $data = wp04Json($kernel->handleServer(wp04Server('GET', '/session')))['data'];
    return (string) ($data['csrf']['token'] ?? '');
}

wp04Test('WP-05 anonymous session probe', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore());
    $data = wp04Json($kernel->handleServer(wp04Server('GET', '/session')))['data'];
    wp04Same(false, $data['authenticated']);
    wp04Assert(!isset($data['csrf']));
});

wp04Test('WP-05 authenticated bootstrap is bounded', static function (): void {
    $session = new Wp05ArraySessionStore(['analyst_id' => 7], 'private-session-id');
    [$kernel] = wp05Kernel($session);
    $body = $kernel->handleServer(wp04Server('GET', '/session'))->body();
    $data = json_decode($body, true)['data'];
    wp04Same(true, $data['authenticated']);
    wp04Same('oidc', $data['actor']['authSource']);
    wp04Assert(strpos($body, 'private-session-id') === false);
    wp04Assert(preg_match('/^[a-f0-9]{64}$/', $data['csrf']['token']) === 1);
});

wp04Test('WP-05 CSRF token binds to Session id', static function (): void {
    $session = new Wp05ArraySessionStore(['analyst_id' => 7], 'session-a');
    [$kernel] = wp05Kernel($session);
    $first = wp05Csrf($kernel);
    wp04Same($first, wp05Csrf($kernel));
    $session->changeId('session-b');
    wp04Assert($first !== wp05Csrf($kernel));
});

wp04Test('WP-05 protected route requires Session', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore());
    $response = $kernel->handleServer(wp04Server('GET', '/protected'));
    wp04Same(401, $response->status());
    wp04Same('unauthenticated', wp04Json($response)['error']['code']);
});

wp04Test('WP-05 stale actor clears authentication state', static function (): void {
    $session = new Wp05ArraySessionStore([
        'analyst_id' => 99,
        'active_tenant_id' => 2,
        'allowed_modules' => ['tickets'],
    ]);
    [$kernel] = wp05Kernel($session, new Wp05FakeSecurityProvider());
    wp04Same(401, $kernel->handleServer(wp04Server('GET', '/protected'))->status());
    wp04Assert(!isset($session->values()['analyst_id']));
    wp04Assert(!isset($session->values()['active_tenant_id']));
});

wp04Test('WP-05 password-change Session is blocked', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore([
        'analyst_id' => 7,
        'password_expired' => true,
    ]));
    $response = $kernel->handleServer(wp04Server('GET', '/protected'));
    wp04Same(403, $response->status());
    wp04Same('password_change_required', wp04Json($response)['error']['code']);
});

wp04Test('WP-05 missing Origin fails before provider resolution', static function (): void {
    $session = new Wp05ArraySessionStore(['analyst_id' => 7]);
    $provider = new Wp05FakeSecurityProvider([7 => Wp05FakeSecurityProvider::state()]);
    [$kernel, $provider] = wp05Kernel($session, $provider);
    $token = wp05Csrf($kernel);
    $before = $provider->resolveCalls;
    $response = $kernel->handleServer(
        wp04Server('POST', '/write', ['X-CSRF-Token' => $token]),
        [],
        '{}'
    );
    wp04Same(403, $response->status());
    wp04Same($before, $provider->resolveCalls);
});

wp04Test('WP-05 foreign Origin is rejected', static function (): void {
    $session = new Wp05ArraySessionStore(['analyst_id' => 7]);
    [$kernel] = wp05Kernel($session);
    $token = wp05Csrf($kernel);
    $response = $kernel->handleServer(
        wp04Server('POST', '/write', [
            'Origin' => 'https://evil.example',
            'X-CSRF-Token' => $token,
        ]),
        [],
        '{}'
    );
    wp04Same('csrf_origin_failed', wp04Json($response)['error']['code']);
});

wp04Test('WP-05 null and user-info Origins are rejected', static function (): void {
    foreach (['null', 'https://user@desk.example.test'] as $origin) {
        $session = new Wp05ArraySessionStore(['analyst_id' => 7]);
        [$kernel] = wp05Kernel($session);
        $token = wp05Csrf($kernel);
        $response = $kernel->handleServer(
            wp04Server('POST', '/write', [
                'Origin' => $origin,
                'X-CSRF-Token' => $token,
            ]),
            [],
            '{}'
        );
        wp04Same('csrf_origin_failed', wp04Json($response)['error']['code']);
    }
});

wp04Test('WP-05 missing CSRF token is rejected', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore(['analyst_id' => 7]));
    wp05Csrf($kernel);
    $response = $kernel->handleServer(
        wp04Server('POST', '/write', ['Origin' => 'https://desk.example.test']),
        [],
        '{}'
    );
    wp04Same('csrf_failed', wp04Json($response)['error']['code']);
});

wp04Test('WP-05 invalid CSRF token is rejected', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore(['analyst_id' => 7]));
    wp05Csrf($kernel);
    $response = $kernel->handleServer(
        wp04Server('POST', '/write', [
            'Origin' => 'https://desk.example.test',
            'X-CSRF-Token' => str_repeat('a', 64),
        ]),
        [],
        '{}'
    );
    wp04Same(403, $response->status());
});

wp04Test('WP-05 Referer fallback accepts same origin', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore(['analyst_id' => 7]));
    $token = wp05Csrf($kernel);
    $response = $kernel->handleServer(
        wp04Server('POST', '/write', [
            'Referer' => 'https://desk.example.test/ui/',
            'X-CSRF-Token' => $token,
        ]),
        [],
        '{}'
    );
    wp04Same(200, $response->status());
});

wp04Test('WP-05 exact Origin and token allow write', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore(['analyst_id' => 7]));
    $token = wp05Csrf($kernel);
    $response = $kernel->handleServer(
        wp04Server('POST', '/write', [
            'Origin' => 'https://desk.example.test',
            'X-CSRF-Token' => $token,
        ]),
        [],
        '{}'
    );
    wp04Same(200, $response->status());
});

wp04Test('WP-05 capability policy is authoritative', static function (): void {
    $state = Wp05FakeSecurityProvider::state(['capabilities' => []]);
    [$denied] = wp05Kernel(
        new Wp05ArraySessionStore(['analyst_id' => 7]),
        new Wp05FakeSecurityProvider([7 => $state])
    );
    wp04Same(403, $denied->handleServer(wp04Server('GET', '/capability'))->status());
    [$allowed] = wp05Kernel(new Wp05ArraySessionStore(['analyst_id' => 7]));
    wp04Same(200, $allowed->handleServer(wp04Server('GET', '/capability'))->status());
});

wp04Test('WP-05 module policy denies missing module', static function (): void {
    $state = Wp05FakeSecurityProvider::state(['modules' => []]);
    [$kernel] = wp05Kernel(
        new Wp05ArraySessionStore(['analyst_id' => 7]),
        new Wp05FakeSecurityProvider([7 => $state])
    );
    wp04Same(403, $kernel->handleServer(wp04Server('GET', '/module'))->status());
});

wp04Test('WP-05 tenant parameter policy denies inaccessible tenant', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore(['analyst_id' => 7]));
    wp04Same(403, $kernel->handleServer(wp04Server('GET', '/tenants/99'))->status());
});

wp04Test('WP-05 object denial is hidden as 404', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore(['analyst_id' => 7]));
    wp04Same(404, $kernel->handleServer(wp04Server('GET', '/objects/2'))->status());
});

wp04Test('WP-05 tenant switch validates input', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore(['analyst_id' => 7]));
    $token = wp05Csrf($kernel);
    $response = $kernel->handleServer(
        wp04Server('POST', '/session/tenant', [
            'Origin' => 'https://desk.example.test',
            'X-CSRF-Token' => $token,
            'Content-Type' => 'application/json',
        ]),
        [],
        '{"tenantId":0}'
    );
    wp04Same(422, $response->status());
});

wp04Test('WP-05 tenant switch denies inaccessible company', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore(['analyst_id' => 7]));
    $token = wp05Csrf($kernel);
    $response = $kernel->handleServer(
        wp04Server('POST', '/session/tenant', [
            'Origin' => 'https://desk.example.test',
            'X-CSRF-Token' => $token,
            'Content-Type' => 'application/json',
        ]),
        [],
        '{"tenantId":99}'
    );
    wp04Same(403, $response->status());
});

wp04Test('WP-05 tenant switch updates context and rotates CSRF', static function (): void {
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore(['analyst_id' => 7]));
    $token = wp05Csrf($kernel);
    $response = $kernel->handleServer(
        wp04Server('POST', '/session/tenant', [
            'Origin' => 'https://desk.example.test',
            'X-CSRF-Token' => $token,
            'Content-Type' => 'application/json',
        ]),
        [],
        '{"tenantId":2}'
    );
    $data = wp04Json($response)['data'];
    wp04Same(2, $data['activeTenant']['id']);
    wp04Assert($token !== $data['csrf']['token']);
});

wp04Test('WP-05 invalid Host is rejected', static function (): void {
    $server = wp04Server('GET', '/session');
    $server['HTTP_HOST'] = "bad\r\nhost";
    [$kernel] = wp05Kernel(new Wp05ArraySessionStore());
    wp04Same(400, $kernel->handleServer($server)->status());
});

wp04Test('WP-05 production unsafe routes require CSRF', static function (): void {
    [$runtime] = wp05Runtime(new Wp05ArraySessionStore());
    foreach (uiApiRoutes($runtime) as $route) {
        if (!in_array($route->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            wp04Assert($route->security()->csrfRequired(), 'Unsafe production route lacks CSRF policy.');
        }
    }
});
