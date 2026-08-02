<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Tenant;
use App\Module\Settings\ModulePlatformSettings;
use App\Module\Settings\ModuleTenantSettings;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * 模块 ServiceProvider 基类。
 *
 * 所有 lasaas-module 的 ServiceProvider 应继承此类，以获得标准生命周期钩子。
 *
 * 生命周期：
 *   磁盘同步 → inactive → enable（首次：install） → active
 *                               → disable → inactive
 *                               → uninstall（回滚迁移 + 清理数据）→ 删除记录
 *
 * 租户侧生命周期（模块支持 tenant 区域后，由中央管理员按租户安装）：
 *   module:tenant-enable（首次：tenantInstall） → tenant_modules.enabled = true
 *                                                  → tenantOnEnable
 *                                     → 禁用 → tenantOnDisable
 *   module:tenant-uninstall（tenantUninstall：回滚租户库迁移）→ 删除记录
 *
 * 迁移目录约定：
 *   database/migrations        —— 中央迁移（install/uninstall 默认运行/回滚）
 *   database/migrations/tenant —— 租户迁移（tenantInstall/tenantUninstall 默认运行/回滚）
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * 模块首次启用时执行（仅一次，中央上下文）。
     *
     * 默认按约定运行包内 database/migrations 目录下的中央迁移；
     * 有特殊需求的模块可覆盖此方法。
     */
    public function install(): void
    {
        $this->runMigrations('database/migrations');
    }

    /**
     * 模块卸载时执行（中央上下文）。
     *
     * 默认按约定回滚包内 database/migrations 目录下的中央迁移。
     */
    public function uninstall(): void
    {
        $this->rollbackMigrations('database/migrations');
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
     * 默认按约定运行包内 database/migrations/tenant 目录下的租户迁移；
     * 有特殊需求的模块可覆盖此方法。
     */
    public function tenantInstall(Tenant $tenant): void
    {
        $this->runMigrations('database/migrations/tenant');
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
     * 默认按约定回滚包内 database/migrations/tenant 目录下的租户迁移。
     */
    public function tenantUninstall(Tenant $tenant): void
    {
        $this->rollbackMigrations('database/migrations/tenant');
    }

    // ---------------------------------------------------------------
    // 迁移约定
    // ---------------------------------------------------------------

    /**
     * 运行包内指定目录的迁移。
     *
     * 目录不存在时静默跳过（模块可能只有中央或只有租户迁移）。
     */
    protected function runMigrations(string $relativePath): void
    {
        $path = $this->packagePath($relativePath);

        if (is_dir($path)) {
            $this->app['migrator']->run($path);
        }
    }

    /**
     * 回滚包内指定目录的迁移。
     */
    protected function rollbackMigrations(string $relativePath): void
    {
        $path = $this->packagePath($relativePath);

        if (is_dir($path)) {
            $this->app['migrator']->rollback($path);
        }
    }

    /**
     * 解析模块包根目录下相对路径的绝对路径。
     *
     * 从 Provider 文件所在目录向上查找 composer.json（lasaas-module 包根），
     * 使迁移约定不依赖 Provider 在包内的具体位置。
     */
    protected function packagePath(string $path = ''): string
    {
        $dir = dirname((new ReflectionClass($this))->getFileName());

        while ($dir !== '' && $dir !== DIRECTORY_SEPARATOR) {
            if (file_exists($dir.'/composer.json')) {
                return $path === '' ? $dir : rtrim($dir, '/').'/'.ltrim($path, '/');
            }

            $dir = dirname($dir);
        }

        return $path;
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
