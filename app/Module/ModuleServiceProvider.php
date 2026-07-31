<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Tenant;
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
     * 模块运行时配置的根 key（config() 读取时使用）。
     *
     * 返回后，中央设置（modules.settings）与租户设置（tenant_modules.settings）
     * 会按「模块默认配置 → 中央设置 → 租户设置」的优先级合并到该 key 下。
     *
     * 例如博客模块返回 'blog'，控制器通过 config('blog.per_page') 读取。
     * 返回 null 表示该模块不做运行时配置合并。
     */
    public function configKey(): ?string
    {
        return null;
    }

    /**
     * 中央设置表单结构（后台「模块 → 设置」页使用）。
     *
     * 返回 Filament Form 组件数组，字段名即 modules.settings JSON 的 key，
     * 合并进 configKey() 指向的模块配置。
     *
     * 与 tenantSettingsSchema() 不同——这里定义的是「对所有租户统一生效」的全局设置，
     * 例如默认每页条数、是否开启评论等平台级选项。
     */
    public function centralSettingsSchema(): array
    {
        return [];
    }

    /**
     * 租户设置表单结构（后台「租户 → 模块管理」页使用）。
     *
     * 返回 Filament Form 组件数组，字段名即 tenant_modules.settings JSON 的 key，
     * 会覆盖中央设置在 configKey() 指向的模块配置上的值。
     *
     * 与 centralSettingsSchema() 不同——这里定义的是「某个租户自己的模块设置」，
     * 例如该租户的每页条数、主题色、展示选项等，可包含与中央设置相同或不同的选项。
     */
    public function tenantSettingsSchema(): array
    {
        return [];
    }
}
