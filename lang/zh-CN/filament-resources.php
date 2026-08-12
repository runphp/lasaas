<?php

declare(strict_types=1);

return [
    'team' => [
        'label' => '团队管理',
        'model' => [
            'label' => '团队',
        ],
    ],
    'plan' => [
        'label' => '套餐管理',
        'model' => [
            'label' => '套餐',
        ],
        'sections' => [
            'basic' => '基本信息',
            'pricing' => '定价',
            'features' => '功能特性',
            'display' => '展示设置',
        ],
        'hints' => [
            'badge' => '例如：推荐、热门',
            'original_price' => '划线原价，不参与折扣可留空',
        ],
        'key_value' => [
            'feature' => '功能',
            'limit' => '限制',
            'add_feature' => '添加功能',
        ],
    ],
    'tenant' => [
        'label' => '租户管理',
        'model' => [
            'label' => '租户',
        ],
        'database' => [
            'section' => '数据库连接',
            'description' => '手动指定该租户使用的数据库，数据库需提前创建好',
            'advanced' => '高级选项',
            'advanced_description' => '可选，留空则使用 config/database.php 中该连接的默认配置',
            'options_key_label' => '选项',
            'options_value_label' => '值',
            'placeholders' => [
                'prefix_indexes' => '使用默认',
                'strict' => '使用默认',
                'engine' => '如 InnoDB',
                'host' => '留空则使用默认配置',
                'port' => '留空则使用默认配置',
            ],
            'hints' => [
                'domain' => '如：myshop.tenant.ddev.site',
                'domain_add' => '添加域名',
                'database_sqlite' => 'SQLite 文件数据库，如 database/shop_001.sqlite',
                'database_non_sqlite' => '该数据库需提前在数据库服务器上创建好',
                'connection_sqlite_summary' => 'SQLite 使用本地文件数据库，只需填写"数据库名"（文件名），文件保存在 Laravel 的 database_path() 目录下。',
            ],
        ],
    ],
    'user' => [
        'label' => '用户管理',
        'model' => [
            'label' => '用户',
        ],
    ],
    'system' => [
        'group' => '系统管理',
    ],
    'module' => [
        'label' => '模块管理',
        'model' => [
            'label' => '模块',
        ],
        'sections' => [
            'basic' => '基本信息',
            'provider' => '服务提供者',
            'ordering' => '加载配置',
        ],
        'hints' => [
            'package_name' => 'Composer 包名，如 my-saas/module-blog',
            'weight' => '数值越小加载优先级越高，默认0',
            'dependencies' => '由 module:sync 命令自动分析composer依赖得出',
            'after' => '非强依赖，但需要在这些模块加载完成后再加载',
        ],
        'notify' => [
            'toggle_title' => '模块 :pkg 已:status',
        ],
        'settings' => [
            'label' => '模块设置',
            'saved' => '设置已保存',
        ],
        'uninstall' => [
            'label' => '卸载模块',
            'modal' => [
                'heading' => '卸载模块 :label',
                'description' => '卸载模块将删除所有相关数据，且无法恢复，请谨慎操作。',
                'actions' => [
                    'uninstall' => [
                        'label' => '卸载',
                    ],
                    'cancel' => [
                        'label' => '取消',
                    ],
                ],
            ],
            'notify' => [
                'success' => '模块 :pkg 已被卸载',
                'fail' => '卸载模块失败',
                'fail_body' => '存在租户已启用此模块，请先禁用它们后再卸载',
            ],
        ],
    ],
];
