<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

uiApiBuildKernel()->handleServer($_SERVER, $_GET, null)->send();
