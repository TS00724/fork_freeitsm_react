<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

// One front controller owns all executable /api/ui/v1 routes. The local
// openapi.json file remains a static contract artifact, not a second API stack.
uiApiBuildKernel()->handleServer($_SERVER, $_GET, null)->send();
