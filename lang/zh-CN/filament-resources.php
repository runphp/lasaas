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
    ],
    'page' => [
        'label' => '页面管理',
        'model' => [
            'label' => '页面',
        ],
    ],
    'tenant' => [
        'label' => '租户管理',
        'model' => [
            'label' => '租户',
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
