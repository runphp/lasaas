<?php

declare(strict_types=1);

namespace App\Module;

use Illuminate\Support\ServiceProvider;

/**
 * 模块 ServiceProvider 基类。
 *
 * 所有 lasaas-module 的 ServiceProvider 应继承此类，以获得标准生命周期钩子。
 *
 * 生命周期：
 *   磁盘同步 → inactive → enable（首次：install） → active
 *                               → disable → inactive
 *                               → uninstall（回滚迁移 + 清理数据）→ 删除记录
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * 模块首次启用时执行（仅一次）。
     *
     * 适合执行：运行迁移、创建默认数据、发布资源等。
     */
    public function install(): void
    {
        //
    }

    /**
     * 模块卸载时执行。
     *
     * 适合执行：回滚迁移、删除业务数据、清理资源等。
     * 注意：此时代码可能已被删除，应尽量稳健。
     */
    public function uninstall(): void
    {
        //
    }

    /**
     * 每次启用时执行（install 之后也会调用）。
     */
    public function onEnable(): void
    {
        //
    }

    /**
     * 每次禁用时执行。
     */
    public function onDisable(): void
    {
        //
    }
}
