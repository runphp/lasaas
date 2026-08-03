<?php

declare(strict_types=1);

return [
    'module' => [
        // 基础字段
        'id' => 'ID',
        'package_name' => 'Composer Package Name',
        'name' => 'Name',
        'description' => 'Description',
        'version' => 'Version',
        'providers' => 'Service Providers',
        'weight' => 'Load Weight',
        'dependencies' => 'Dependency Modules',
        'after' => 'Post-Load Modules',
        'areas' => 'Effective Areas',
        'path' => 'Disk Path',
        'status' => 'Status',
        'installed_at' => 'First Installation Time',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',

        // 状态枚举
        'statuses' => [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ],

        // 区域枚举
        'area_options' => [
            'central' => 'Central Application',
            'tenant' => 'Tenant Application',
        ],
    ],
    'page' => [
        'slug' => 'Slug',
        'title' => 'Title',
        'layout' => 'Layout',
        'meta' => 'Meta',
        'content' => 'Content',
        'is_published' => 'Is Published',
    ],
    'tenant' => [
        'id' => 'ID',
        'name' => 'Name',
        'domains' => 'Domains',
        'email' => 'Email',
        'phone' => 'Phone',
        'user' => 'User',
        'team' => 'Team',
        'status' => 'Status',
        'expired_at' => 'Expired At',
        'statuses' => [
            'pending' => 'Pending',
            'active' => 'Active',
            'suspended' => 'Suspended',
            'expired' => 'Expired',
            'disabled' => 'Disabled',
        ],
        'database' => [
            'connection' => 'Database Type',
            'database' => 'Database Name',
            'host' => 'Host',
            'port' => 'Port',
            'username' => 'Username',
            'password' => 'Password',
            'charset' => 'Charset',
            'collation' => 'Collation',
            'unix_socket' => 'Unix Socket',
            'prefix' => 'Table Prefix',
            'prefix_indexes' => 'Use Prefix Indexes',
            'strict' => 'Strict Mode',
            'engine' => 'Storage Engine',
            'options' => 'PDO Options',
            'prefix_indexes_options' => [
                '1' => 'Yes',
                '0' => 'No',
            ],
            'strict_options' => [
                '1' => 'Enabled',
                '0' => 'Disabled',
            ],
        ],
        'connection_types' => [
            'mariadb' => 'MariaDB',
            'mysql' => 'MySQL',
            'pgsql' => 'PostgreSQL',
            'sqlite' => 'SQLite (File Database)',
        ],
    ],
    'team' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'is_personal' => 'Personal',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
    ],
    'plan' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'badge' => 'Badge',
        'price' => 'Price',
        'original_price' => 'Original Price',
        'billing_cycle' => 'Billing Cycle',
        'features' => 'Features',
        'sort_order' => 'Sort Order',
        'is_featured' => 'Featured',
        'is_active' => 'Active',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'user' => [
        'name' => 'Name',
        'email' => 'Email address',
        'email_verified_at' => 'Email Verified At',
        'roles' => [
            'name' => 'Roles',
        ],
        'current_team' => [
            'name' => 'Current Team',
        ],
        'password' => 'Password',
        'two_factor_secret' => 'Two Factor Secret',
        'two_factor_recovery_codes' => 'Two Factor Recovery Codes',
        'two_factor_confirmed_at' => 'Two Factor Confirmed At',
        'text' => [
            'has_two_factor' => 'Two Factor Authentication Configured',
            'has_recovery_codes' => 'Recovery Codes Generated',
        ],
        'placeholders' => [
            'empty' => '-',
        ],
    ],
];
