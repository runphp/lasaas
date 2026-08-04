<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Tenant;
use App\Module\Settings\ModulePlatformSettings;
use App\Module\Settings\ModuleTenantSettings;
use Illuminate\Support\ServiceProvider;

/**
 * 模块 ServiceProvider 基类。
 *
 * 所有 lasaas-module 的 ServiceProvider 应继承此类，以获得标准生命周期钩子。
 *
 * 生命周期：
 *   磁盘同步 → inactive → enable（首次：install） → active
 *                               → disable → inactive
 *                               → uninstall（清理数据）→ 删除记录
 *
 * 租户侧生命周期（模块支持 tenant 区域后，由中央管理员按租户安装）：
 *   module:tenant-enable（首次：tenantInstall） → tenant_modules.enabled = true
 *                                                  → tenantOnEnable
 *                                     → 禁用 → tenantOnDisable
 *   module:tenant-uninstall（tenantUninstall）→ 删除记录
 *
 * 迁移不再由本类处理：模块迁移统一由 ModuleMigrationService 执行，
 * 记录到独立表 module_migrations（module_id + 模块内独立 batch），
 * 与系统 migrations 表完全隔离。install/uninstall/tenantInstall/tenantUninstall
 * 仅作为可覆盖的空钩子，供模块做数据播种、自定义清理等非迁移逻辑。
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * 模块首次启用时执行（仅一次，中央上下文）。
     *
     * 迁移已由 ModuleMigrationService 自动执行；本钩子仅供需要
     * 自定义安装逻辑（如播种默认数据）的模块覆盖。
     */
    public function install(): void
    {
        //
    }

    /**
     * 模块卸载时执行（中央上下文）。
     *
     * 迁移已由 ModuleMigrationService 自动回滚；本钩子仅供需要
     * 自定义清理逻辑的模块覆盖。
     */
    public function uninstall(): void
    {
        //
    }

    /**
     * 每次启用时执行（install 之后也会调用，中央上下文）。
     */
    public function onEnable(): void
    {
        //
    }

    /**
     * 每次禁用时执行（中央上下文）。
     */
    public function onDisable(): void
    {
        //
    }

    /**
     * 模块首次安装到指定租户时执行（租户上下文已初始化）。
     *
     * 租户迁移已由 ModuleMigrationService 自动执行；本钩子仅供需要
     * 自定义租户安装逻辑的模块覆盖。
     */
    public function tenantInstall(Tenant $tenant): void
    {
        //
    }

    /**
     * 每次在指定租户启用时执行（tenantInstall 之后也会调用，租户上下文已初始化）。
     */
    public function tenantOnEnable(Tenant $tenant): void
    {
        //
    }

    /**
     * 在指定租户禁用时执行。
     */
    public function tenantOnDisable(Tenant $tenant): void
    {
        //
    }

    /**
     * 模块从指定租户卸载时执行（租户上下文已初始化）。
     *
     * 租户迁移已由 ModuleMigrationService 自动回滚；本钩子仅供需要
     * 自定义租户清理逻辑的模块覆盖。
     */
    public function tenantUninstall(Tenant $tenant): void
    {
        //
    }

    // ---------------------------------------------------------------
    // 设置（Settings）
    // ---------------------------------------------------------------

    /**
     * 模块设置类声明。
     *
     * 以「设置类型 key => 设置类」映射数组返回模块的所有设置类，例如：
     *
     *     return [
     *         'platform' => ExamplePlatformSettings::class,
     *         'tenant'   => ExampleTenantSettings::class,
     *     ];
     *
     * 内置设置类型：
     *  - platform：平台级设置，后台「模块 → 设置」页编辑，持久化到中央库 settings 表，
     *    group 固定为 "module:{groupKey}"，对所有租户统一生效；
     *  - tenant：租户级设置，后台「租户 → 模块管理」页编辑，
     *    group 为 "tenant_module:{tenant_id}:{groupKey}"，按租户隔离。
     *
     * 后续新增设置类型时只需约定新的 key 与对应的设置基类。
     *
     * @return array{
     *     platform?: class-string<ModulePlatformSettings>,
     *     tenant?: class-string<ModuleTenantSettings>,
     * }
     */
    public function settingsClasses(): array
    {
        return [];
    }
}
