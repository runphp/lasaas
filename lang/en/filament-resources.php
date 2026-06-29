<?php

declare(strict_types=1);

return [
    'team' => [
        'label' => 'Team Management',
    ],
    'plan' => [
        'label' => 'Plan Management',
    ],
    'page' => [
        'label' => 'Page Management',
    ],
    'tenant' => [
        'label' => 'Tenant Management',
        'statuses' => [
            'pending' => 'Pending',
            'active' => 'Active',
            'suspended' => 'Suspended',
            'expired' => 'Expired',
            'disabled' => 'Disabled',
        ],
    ],
    'user' => [
        'label' => 'User Management',
    ],
    'system' => [
        'group' => 'System Management',
    ],
    'module' => [
        'label' => 'Module Management',
        'statuses' => [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ],
        'areas' => [
            'central' => 'Central',
            'tenant' => 'Tenant',
        ],
        'sections' => [
            'basic' => 'Basic Info',
            'provider' => 'Service Provider',
            'ordering' => 'Load Ordering',
        ],
        'fields' => [
            'package_name' => 'Package Name',
            'name' => 'Module Name',
            'description' => 'Description',
            'version' => 'Version',
            'provider_class' => 'Provider Class',
            'weight' => 'Weight',
            'dependencies' => 'Dependencies',
            'after' => 'After',
            'areas' => 'Areas',
            'path' => 'Path',
            'status' => 'Status',
        ],
        'helpers' => [
            'package_name' => 'Composer package name, e.g. my-saas/module-blog',
            'weight' => 'Lower loads first, default 0',
            'dependencies' => 'Auto-analyzed from composer require by modules:sync',
            'after' => 'Non-hard dependency but must load after these modules',
        ],
    ],
];
