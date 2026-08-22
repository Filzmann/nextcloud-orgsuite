<?php

declare(strict_types=1);

return [
    'routes' => [
        ['name' => 'entry#ad', 'url' => '/ad', 'verb' => 'GET'],
        ['name' => 'entry#br', 'url' => '/br', 'verb' => 'GET'],
        ['name' => 'external_link_admin#settings', 'url' => '/api/admin/external-links', 'verb' => 'GET'],
        ['name' => 'external_link_admin#save', 'url' => '/api/admin/external-links', 'verb' => 'POST'],
    ],
];
