<?php

declare(strict_types=1);

namespace FreeITSM\UiApi\V1;

return [
    new UiRoute('GET', '/', 'uiApiIndex', __NAMESPACE__ . '\\uiApiIndexHandler'),
    new UiRoute('GET', '/health', 'uiApiHealth', __NAMESPACE__ . '\\uiApiHealthHandler'),
];
