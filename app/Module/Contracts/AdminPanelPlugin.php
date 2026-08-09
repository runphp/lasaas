<?php

declare(strict_types=1);

namespace App\Module\Contracts;

use Filament\Contracts\Plugin;

/**
 * 中央 admin 面板插件契约。
 *
 * 约定（约定大于配置）：
 *  - 模块中实现本接口的类 = 该模块对中央 admin 面板的扩展；
 *  - 类放在模块 PSR-4 根命名空间下的 Filament\Plugins 子命名空间
 *    （目录通常为 src/Filament/Plugins/），由 ModuleDiscoveryManager 自动发现；
 *  - 无需在 composer.json 的 extra.lasaas-module.plugins 中声明。
 *
 * 实现本接口即实现 Filament\Contracts\Plugin，需提供：
 *  - getId(): string —— 建议从模块包名派生（lasaas/blog → lasaas-blog），
 *    即 str_replace('/', '-', 包名)，避免不同模块的插件 ID 重复
 *  - register(Panel $panel): void
 *  - boot(Panel $panel): void
 */
interface AdminPanelPlugin extends Plugin {}
