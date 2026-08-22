<?php
/**
 * FreeITSM browser UI API v1 front controller.
 *
 * This is deliberately separate from /api/v1: no Bearer API key, no wildcard
 * CORS, and no business route exists in WP-04. Session/CSRF/tenant/RBAC
 * enforcement is introduced in WP-05 through the request-context boundary.
 */

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

use FreeITSM\UiApi\V1\UiRequest;

$kernel = freeitsmUiApiKernel();
$response = $kernel->handleGlobals();
$response->emit(strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'HEAD');
