<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| 示例模块配置
|--------------------------------------------------------------------------
|
| 本文件是模块「默认配置」的示例。config() 读取键为 `lasaas.example.*`
| （包名中的 / 转为 .）。例如：config('lasaas.example.title')。
|
| 注意：这里放的是**静态默认配置**（代码级默认值），不适合在后台编辑。
| 需要后台可编辑、按租户隔离的「模块设置」，请基于 spatie/laravel-settings
| 定义设置类并在 ServiceProvider 中声明（见 packages/README.md「配置与设置」）。
|
*/

return [

    // 是否启用演示功能
    'enabled' => env('LASAAS_EXAMPLE_ENABLED', true),

    // 模块标题
    'title' => env('LASAAS_EXAMPLE_TITLE', 'Hello Lasaas'),

    // 模块简介
    'description' => '这是一个示例模块，用于演示 lasaas-module 的标准结构。',

    // 每页默认条数
    'per_page' => 15,

    // 允许的展示模式
    'display_modes' => [
        'list',
        'grid',
        'cards',
    ],

    // 默认展示模式
    'default_display_mode' => 'list',

    // 演示接口开关（模块本身不提供接口，仅为示例）
    'api' => [
        'enabled' => env('LASAAS_EXAMPLE_API_ENABLED', false),
        'rate_limit' => 60,
    ],
];
