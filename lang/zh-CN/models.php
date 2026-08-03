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
        'created_at' => '创建时间',
        'updated_at' => '更新时间',

        // 状态枚举
        'statuses' => [
            'active' => '启用',
            'inactive' => '禁用',
        ],

        // 区域枚举
        'area_options' => [
            'central' => '中央应用',
            'tenant' => '租户应用',
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
        'database' => [
            'connection' => '数据库类型',
            'database' => '数据库名',
            'host' => '主机',
            'port' => '端口',
            'username' => '用户名',
            'password' => '密码',
            'charset' => '字符集',
            'collation' => '排序规则',
            'unix_socket' => 'Unix Socket',
            'prefix' => '表前缀',
            'prefix_indexes' => '索引是否使用前缀',
            'strict' => '严格模式',
            'engine' => '存储引擎',
            'options' => 'PDO 选项',
            'prefix_indexes_options' => [
                '1' => '使用',
                '0' => '不使用',
            ],
            'strict_options' => [
                '1' => '启用',
                '0' => '关闭',
            ],
        ],
        'connection_types' => [
            'mariadb' => 'MariaDB',
            'mysql' => 'MySQL',
            'pgsql' => 'PostgreSQL',
            'sqlite' => 'SQLite（文件数据库）',
        ],
    ],
    'team' => [
        'name' => '团队名称',
        'slug' => '标识',
        'is_personal' => '是否个人团队',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
        'deleted_at' => '删除时间',
    ],
    'plan' => [
        'name' => '套餐名称',
        'slug' => '标识',
        'description' => '描述',
        'badge' => '徽章',
        'price' => '价格',
        'original_price' => '原价',
        'billing_cycle' => '计费周期',
        'features' => '功能特性',
        'sort_order' => '排序',
        'is_featured' => '是否推荐',
        'is_active' => '是否启用',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
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
