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
        'placeholders' => [
            'installed_at' => 'Not Installed',
        ],
        // 状态枚举
        'statuses' => [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ],

        // 区域枚举
        'areas' => [
            'central' => 'Central Application',
            'tenant' => 'Tenant Application',
        ],

        // 表单分组标题
        'sections' => [
            'basic' => 'Basic Information',
            'provider' => 'Service Providers',
            'ordering' => 'Loading Configuration',
        ],

        // 输入框提示文案 helpers
        'hints' => [
            'package_name' => 'Composer Package Name, e.g., my-saas/module-blog',
            'weight' => 'The smaller the value, the higher the loading priority. Default is 0.',
            'dependencies' => 'Automatically analyzed from composer dependencies by the module:sync command.',
            'after' => 'Not a strong dependency, but needs to be loaded after these modules are completed.',
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
