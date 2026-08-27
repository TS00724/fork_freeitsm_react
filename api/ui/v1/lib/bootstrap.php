<?php

declare(strict_types=1);

$repositoryRoot = dirname(__DIR__, 4);
if (is_file($repositoryRoot . '/config.php')) require_once $repositoryRoot . '/config.php';
require_once $repositoryRoot . '/includes/session_security.php';
require_once __DIR__ . '/UiApiServerOrigin.php';
require_once __DIR__ . '/Http.php';
require_once __DIR__ . '/UiApiSessionStore.php';
require_once __DIR__ . '/UiApiCsrfGuard.php';
require_once __DIR__ . '/UiApiSecurityProvider.php';
require_once __DIR__ . '/UiApiRouteSecurity.php';
require_once __DIR__ . '/UiApiLegacyDatabase.php';
require_once __DIR__ . '/UiApiLegacyAuthorization.php';
require_once __DIR__ . '/UiApiLegacyTenancy.php';
require_once __DIR__ . '/UiApiLegacySecurityProvider.php';
require_once __DIR__ . '/UiApiSecurityRuntime.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/handlers.php';
require_once __DIR__ . '/routes.php';

ini_set('display_errors', '0');
ini_set('html_errors', '0');
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function uiApiBuildSecurityRuntime(): UiApiSecurityRuntime
{
    return new UiApiSecurityRuntime(new UiApiNativeSessionStore(), new UiApiLegacySecurityProvider(), new UiApiCsrfGuard());
}

/** @param UiApiRoute[]|null $routes */
function uiApiBuildKernel(?array $routes = null, ?UiApiSecurityRuntime $security = null): UiApiKernel
{
    $security = $security ?? uiApiBuildSecurityRuntime();
    $routes = $routes ?? uiApiRoutes($security);
    return new UiApiKernel(new UiApiRouter($routes, $security));
}
