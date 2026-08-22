<?php

declare(strict_types=1);

// WP-04 intentionally does not load the application database config, start a
// PHP Session, or reuse the Bearer API-key machine bootstrap. WP-05 must bind
// those dependencies behind the reviewed request context.
require_once __DIR__ . '/Http.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/handlers.php';

ini_set('display_errors', '0');
ini_set('html_errors', '0');
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

/** @param UiApiRoute[]|null $routes */
function uiApiBuildKernel(?array $routes = null): UiApiKernel
{
    if ($routes === null) $routes = require __DIR__ . '/routes.php';
    return new UiApiKernel(new UiApiRouter($routes));
}
