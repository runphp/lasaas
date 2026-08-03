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
            'notify' => [
                'success' => 'Module :pkg has been uninstalled',
                'fail' => 'Failed to uninstall module',
                'fail_body' => 'There are tenants with this module enabled, disable them first before uninstalling',
            ],
        ],
    ],
];
