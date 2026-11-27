<?php

declare(strict_types=1);

return [
    'routes' => [
        ['name' => 'entry#flz', 'url' => '/flz', 'verb' => 'GET'],
        ['name' => 'entry#br', 'url' => '/br', 'verb' => 'GET'],
        ['name' => 'external_link_admin#settings', 'url' => '/api/admin/external-links', 'verb' => 'GET'],
        ['name' => 'external_link_admin#save', 'url' => '/api/admin/external-links', 'verb' => 'POST'],
        ['name' => 'suite_label_admin#settings', 'url' => '/api/admin/suite-label', 'verb' => 'GET'],
        ['name' => 'suite_label_admin#save', 'url' => '/api/admin/suite-label', 'verb' => 'POST'],
    ],
];
