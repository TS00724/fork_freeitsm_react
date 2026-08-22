<?php

declare(strict_types=1);

function uiApiFoundationHandler(UiApiRequest $request, UiApiRequestContext $context, array $parameters): array
{
    unset($request, $parameters);
    return [
        'name' => 'FreeITSM Browser UI API',
        'version' => 'v1',
        'surface' => 'same-origin-browser-bff',
        'authentication' => 'reserved-for-wp-05',
        'routes' => ['GET /', 'GET /health'],
        'security' => $context->unresolvedSecuritySlots(),
    ];
}

function uiApiHealthHandler(UiApiRequest $request, UiApiRequestContext $context, array $parameters): array
{
    unset($request, $context, $parameters);
    return [
        'status' => 'ok',
        'scope' => 'ui-api-process',
        'checks' => ['database' => 'not_checked', 'session' => 'not_checked'],
    ];
}
