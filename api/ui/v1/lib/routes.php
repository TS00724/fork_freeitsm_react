<?php

declare(strict_types=1);

return [
    new UiApiRoute('GET', '#^/$#', 'ui.foundation', 'uiApiFoundationHandler'),
    new UiApiRoute('GET', '#^/health$#', 'ui.health', 'uiApiHealthHandler'),
];
