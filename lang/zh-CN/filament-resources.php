<?php

declare(strict_types=1);

return [
    'team' => [
        'label' => '团队管理',
    ],
    'plan' => [
        'label' => '套餐管理',
    ],
    'page' => [
        'label' => '页面管理',
    ],
    'tenant' => [
        'label' => '租户管理',
        'statuses' => [
            'pending' => '待审核',
            'active' => '正常使用',
            'suspended' => '已暂停',
            'expired' => '已过期',
            'disabled' => '已禁用',
        ],
        'fields' => [
            'name' => '名称',
            'domain' => '域名',
            'email' => '邮箱',
            'phone' => '手机号',
            'team' => '团队',
            'user' => '用户',
            'status' => '状态',
            'expired_at' => '过期时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ],
    ],
    'user' => [
        'label' => '用户管理',
    ],
    'system' => [
        'group' => '系统管理',
    ],
    'module' => [
        'label' => '模块管理',
        'statuses' => [
            'active' => '启用',
            'inactive' => '禁用',
        ],
        'areas' => [
            'central' => '中央应用',
            'tenant' => '租户应用',
        ],
        'sections' => [
            'basic' => '基本信息',
            'provider' => '服务提供者',
            'ordering' => '加载配置',
        ],
        'fields' => [
            'package_name' => '包名',
            'name' => '模块名称',
            'description' => '描述',
            'version' => '版本',
            'providers' => '服务提供者',
            'weight' => '权重',
            'dependencies' => '依赖模块',
            'after' => '排在之后',
            'areas' => '生效区域',
            'path' => '磁盘路径',
            'status' => '状态',
        ],
        'helpers' => [
            'package_name' => 'Composer 包名，如 my-saas/module-blog',
            'weight' => '越小越先加载，默认 0',
            'dependencies' => '由 module:sync 从 composer require 自动分析得出',
            'after' => '非强依赖但必须在这些模块之后加载',
        ],
    ],
];
