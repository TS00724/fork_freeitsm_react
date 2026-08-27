<?php

declare(strict_types=1);

/** @return UiApiRoute[] */
function uiApiRoutes(UiApiSecurityRuntime $security): array
{
    return [
        new UiApiRoute('GET', '#^/$#', 'ui.foundation', 'uiApiFoundationHandler'),
        new UiApiRoute('GET', '#^/health$#', 'ui.health', 'uiApiHealthHandler'),
        new UiApiRoute(
            'GET',
            '#^/session$#',
            'ui.session',
            static function (
                UiApiRequest $request,
                UiApiRequestContext $context,
                array $parameters
            ) use ($security): array {
                return uiApiSessionHandler($security, $request, $context, $parameters);
            },
            [],
            UiApiRouteSecurity::sessionProbe()
        ),
        new UiApiRoute(
            'POST',
            '#^/session/tenant$#',
            'ui.session.tenant',
            static function (
                UiApiRequest $request,
                UiApiRequestContext $context,
                array $parameters
            ) use ($security): array {
                return uiApiSwitchTenantHandler($security, $request, $context, $parameters);
            },
            [],
            UiApiRouteSecurity::authenticatedWrite()
        ),
    ];
}
