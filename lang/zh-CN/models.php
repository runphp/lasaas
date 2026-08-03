<?php

declare(strict_types=1);

return [
    'module' => [
        // 基础字段
        'id' => '主键ID',
        'package_name' => 'Composer包名',
        'name' => '模块名称',
        'description' => '模块描述',
        'version' => '版本号',
        'providers' => '服务提供者',
        'weight' => '加载权重',
        'dependencies' => '依赖模块',
        'after' => '后置加载模块',
        'areas' => '生效区域',
        'path' => '磁盘路径',
        'status' => '状态',
        'installed_at' => '首次安装时间',
        'placeholders' => [
            'installed_at' => '未安装',
        ],
        'created_at' => '创建时间',
        'updated_at' => '更新时间',

        // 状态枚举
        'statuses' => [
            'active' => '启用',
            'inactive' => '禁用',
        ],

        // 区域枚举
        'areas' => [
            'central' => '中央应用',
            'tenant' => '租户应用',
        ],

        // 表单分组标题
        'sections' => [
            'basic' => '基本信息',
            'provider' => '服务提供者',
            'ordering' => '加载配置',
        ],

        // 输入框提示文案
        'hints' => [
            'package_name' => 'Composer 包名，如 my-saas/module-blog',
            'weight' => '数值越小加载优先级越高，默认0',
            'dependencies' => '由 module:sync 命令自动分析composer依赖得出',
            'after' => '非强依赖，但需要在这些模块加载完成后再加载',
        ],
    ],
    'page' => [
        'slug' => 'URL别名',
        'title' => '标题',
        'layout' => '布局',
        'meta' => 'Meta',
        'content' => '内容',
        'is_published' => '是否发布',
    ],
    'tenant' => [
        'id' => 'ID',
        'name' => '租户名称',
        'domains' => '域名',
        'email' => '邮箱',
        'phone' => '手机号',
        'user' => '用户',
        'team' => '团队',
        'status' => '状态',
        'expired_at' => '过期时间',
        'statuses' => [
            'pending' => '待审核',
            'active' => '已启用',
            'suspended' => '已暂停',
            'expired' => '已过期',
            'disabled' => '已禁用',
        ],
    ],
    'user' => [
        'name' => '姓名',
        'email' => '邮箱',
        'email_verified_at' => '邮箱验证时间',
        'roles' => [
            'name' => '角色',
        ],
        'current_team' => [
            'name' => '当前团队',
        ],
        'password' => '密码',
        'two_factor_secret' => '两步验证密钥',
        'two_factor_recovery_codes' => '两步验证恢复码',
        'two_factor_confirmed_at' => '两步验证确认时间',
        'text' => [
            'has_two_factor' => '已配置两步验证',
            'has_recovery_codes' => '已生成恢复码',
        ],
        'placeholders' => [
            'empty' => '-',
        ],
    ],
];
