<?php

declare(strict_types=1);

return [
    'team' => [
        'label' => 'Team Management',
        'model' => [
            'label' => 'Team',
        ],
    ],
    'plan' => [
        'label' => 'Plan Management',
        'model' => [
            'label' => 'Plan',
        ],
    ],
    'page' => [
        'label' => 'Page Management',
        'model' => [
            'label' => 'Page',
        ],
    ],
    'tenant' => [
        'label' => 'Tenant Management',
        'model' => [
            'label' => 'Tenant',
        ],
        'statuses' => [
            'pending' => 'Pending',
            'active' => 'Active',
            'suspended' => 'Suspended',
            'expired' => 'Expired',
            'disabled' => 'Disabled',
        ],
        'fields' => [
            'name' => 'name',
            'domain' => 'domain',
            'email' => 'email',
            'phone' => 'phone',
            'team' => 'team',
            'user' => 'user',
            'status' => 'status',
            'expired_at' => 'expire time',
            'created_at' => 'create time',
            'updated_at' => 'update time',
        ],
    ],
    'user' => [
        'label' => 'User Management',
        'model' => [
            'label' => 'User',
        ],
    ],
    'system' => [
        'group' => 'System Management',
    ],
    'module' => [
        'label' => 'Module Management',
        'model' => [
            'label' => 'Module',
        ],
        'settings' => [
            'label' => 'Settings',
            'saved' => 'Settings saved',
        ],
        'uninstall' => [
            'label' => 'Uninstall Module',
            'modal' => [
                'heading' => 'Uninstall Module :label',
                'description' => 'Uninstalling the module will delete all related data and cannot be undone. Please proceed with caution.',
                'actions' => [
                    'uninstall' => [
                        'label' => 'Uninstall',
                    ],
                    'cancel' => [
                        'label' => 'Cancel',
                    ],
                ],
            ],
        ],
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
            'providers' => 'Provider Classes',
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
            'dependencies' => 'Auto-analyzed from composer require by module:sync',
            'after' => 'Non-hard dependency but must load after these modules',
        ],
    ],
];
