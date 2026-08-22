<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

/** @param array<string,mixed> $params */
function uiApiIndexHandler(UiRequest $request, UiRequestContext $context, array $params): UiRouteResult
{
    return UiRouteResult::success([
        'name' => 'FreeITSM UI API',
        'version' => '1',
        'stage' => 'WP-04 contract foundation',
        'openapi' => 'openapi.json',
        'routes' => ['GET /', 'GET /health'],
        'securityBoundary' => 'Session/CSRF/tenant/RBAC enforcement is deferred to WP-05.',
        'machineApiBoundary' => '/api/v1 remains a separate Bearer API-key surface.',
    ]);
}

/** @param array<string,mixed> $params */
function uiApiHealthHandler(UiRequest $request, UiRequestContext $context, array $params): UiRouteResult
{
    return UiRouteResult::success([
        'status' => 'ok',
        'service' => 'freeitsm-ui-api',
        'version' => '1',
    ]);
}
