<?php

declare(strict_types=1);

return [
    // Navigation infrastructure has no own public privacy/permission provider.
    'providerRegistrations' => [],
    'uiPath' => '/index.php/apps/orgsuite/flz',
    'preGrantUiStatuses' => [303],
    'postGrantUiStatuses' => [303],
    'grantService' => null,
    'permissionProbe' => null,
    'apiSmokes' => [],
];
