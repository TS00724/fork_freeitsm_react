<?php
/**
 * FreeITSM browser UI API v1 bootstrap.
 *
 * WP-04 is intentionally database/session independent. WP-05 will add the
 * authoritative PHP-session and CSRF/tenant/RBAC resolvers at this boundary.
 */

declare(strict_types=1);

use FreeITSM\UiApi\V1\UiApiKernel;
use FreeITSM\UiApi\V1\UiResponseFactory;
use FreeITSM\UiApi\V1\UiRouter;

require_once __DIR__ . '/ApiException.php';
require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/RequestContext.php';
require_once __DIR__ . '/RouteResult.php';
require_once __DIR__ . '/Route.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/HttpResponse.php';
require_once __DIR__ . '/ResponseFactory.php';
require_once __DIR__ . '/Kernel.php';
require_once __DIR__ . '/handlers.php';

ini_set('display_errors', '0');
if (!headers_sent()) {
    header_remove('X-Powered-By');
}

function freeitsmUiApiKernel(): UiApiKernel
{
    /** @var array<int,\FreeITSM\UiApi\V1\UiRoute> $routes */
    $routes = require __DIR__ . '/routes.php';
    return new UiApiKernel(new UiRouter($routes), new UiResponseFactory());
}
