<?php

declare(strict_types=1);

// WP-04 deliberately does not include config.php, start a PHP Session, connect
// to the database, or load the machine API bootstrap. WP-05 will bind those
// dependencies behind the request context after separate security review.
require_once __DIR__ . '/ApiException.php';
require_once __DIR__ . '/HttpResponse.php';
require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/RequestContext.php';
require_once __DIR__ . '/Route.php';
require_once __DIR__ . '/ResponseFactory.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Kernel.php';
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
