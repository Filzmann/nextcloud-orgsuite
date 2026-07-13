<?php

declare(strict_types=1);

return [
    'routes' => [
        ['name' => 'entry#ad', 'url' => '/ad', 'verb' => 'GET'],
        ['name' => 'entry#br', 'url' => '/br', 'verb' => 'GET'],
        ['name' => 'admin_api#settings', 'url' => '/api/admin/settings', 'verb' => 'GET'],
        ['name' => 'admin_api#saveOrganization', 'url' => '/api/admin/organization', 'verb' => 'PUT'],
        ['name' => 'admin_api#savePermissions', 'url' => '/api/admin/permissions', 'verb' => 'PUT'],
    ],
];
