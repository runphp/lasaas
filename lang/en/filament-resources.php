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
        'sections' => [
            'basic' => 'Basic Information',
            'pricing' => 'Pricing',
            'features' => 'Features',
            'display' => 'Display Settings',
        ],
        'hints' => [
            'badge' => 'e.g. Recommended, Hot',
            'original_price' => 'Strikethrough original price, leave empty if no discount',
        ],
        'key_value' => [
            'feature' => 'Feature',
            'limit' => 'Limit',
            'add_feature' => 'Add Feature',
        ],
    ],
    'tenant' => [
        'label' => 'Tenant Management',
        'model' => [
            'label' => 'Tenant',
        ],
        'database' => [
            'section' => 'Database Connection',
            'description' => 'Manually specify the database for this tenant. The database must be created in advance.',
            'advanced' => 'Advanced Options',
            'advanced_description' => 'Optional, leave empty to use the default config from config/database.php',
            'options_key_label' => 'Option',
            'options_value_label' => 'Value',
            'placeholders' => [
                'prefix_indexes' => 'Use Default',
                'strict' => 'Use Default',
                'engine' => 'e.g. InnoDB',
                'host' => 'Use default if empty',
                'port' => 'Use default if empty',
            ],
            'hints' => [
                'domain' => 'e.g. myshop.tenant.ddev.site',
                'domain_add' => 'Add Domain',
                'database_sqlite' => 'SQLite file database, e.g. database/shop_001.sqlite',
                'database_non_sqlite' => 'This database must be created on the database server in advance',
                'connection_sqlite_summary' => 'SQLite uses a local file database. Only requires "Database Name" (filename), files are saved in the Laravel database_path() directory.',
            ],
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
        'sections' => [
            'basic' => 'Basic Information',
            'provider' => 'Service Providers',
            'ordering' => 'Loading Configuration',
        ],
        'hints' => [
            'package_name' => 'Composer Package Name, e.g., my-saas/module-blog',
            'weight' => 'The smaller the value, the higher the loading priority. Default is 0.',
            'dependencies' => 'Automatically analyzed from composer dependencies by the module:sync command.',
            'after' => 'Not a strong dependency, but needs to be loaded after these modules are completed.',
        ],
        'notify' => [
            'toggle_title' => 'Module :pkg has been :status',
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
