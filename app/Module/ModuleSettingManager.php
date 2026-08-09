<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Module;
use App\Models\Tenant;
use App\Module\Settings\ModulePlatformSettings;
use App\Module\Settings\ModuleSettingsScope;
use App\Module\Settings\ModuleTenantSettings;
use Spatie\LaravelSettings\Models\SettingsProperty;

/**
 * 模块设置管理器 —— 承载全部 Spatie LaravelSettings 模块设置逻辑。
 *
 * 职责：
 * - platformSettingsClass()/tenantSettingsClass()/settingsClasses()：设置类解析
 * - resolvePlatformSettings()/resolveTenantSettings()：设置实例解析
 * - deleteSettingsFor()：卸载模块删除设置记录
 * - platformSettingsSchema()/tenantSettingsSchema()：Filament 设置表单结构
 */
class ModuleSettingManager
{
    public function __construct(
        protected ModuleDiscoveryManager $discovery,
    ) {}

    // ---------------------------------------------------------------
    // 设置类解析
    // ---------------------------------------------------------------

    /**
     * 获取模块的平台设置类名。
     *
     * @return class-string<ModulePlatformSettings>|null
     */
    public function platformSettingsClass(Module $module): ?string
    {
        $class = $this->settingsClasses($module)['platform'] ?? null;

        return $class && is_subclass_of($class, ModulePlatformSettings::class) ? $class : null;
    }

    /**
     * 获取模块的租户设置类名。
     *
     * @return class-string<ModuleTenantSettings>|null
     */
    public function tenantSettingsClass(Module $module): ?string
    {
        $class = $this->settingsClasses($module)['tenant'] ?? null;

        return $class && is_subclass_of($class, ModuleTenantSettings::class) ? $class : null;
    }

    /**
     * 获取模块声明的设置类映射（「设置类型 key => 设置类」）。
     *
     * @return array<string, class-string>
     */
    protected function settingsClasses(Module $module): array
    {
        $provider = $this->discovery->resolveModuleProvider($module);

        return $provider instanceof ModuleServiceProvider ? $provider->settingsClasses() : [];
    }

    // ---------------------------------------------------------------
    // 设置实例解析
    // ---------------------------------------------------------------

    /**
     * 解析模块平台设置实例（读取中央库 settings 表中 "module:{groupKey}" 分组）。
     */
    public function resolvePlatformSettings(Module $module): ?ModulePlatformSettings
    {
        $class = $this->platformSettingsClass($module);

        return $class ? new $class : null;
    }

    /**
     * 解析模块在指定租户下的设置实例（读取 "tenant_module:{tenant_id}:{groupKey}" 分组）。
     *
     * 模块包内已声明独立的租户设置类（继承 ModuleTenantSettings），直接实例化即可。
     * 租户分组由 ModuleTenantSettings::group() 根据 ModuleSettingsScope 解析，
     * 这里先设置作用域指向目标租户，再实例化（实例加载是即时的，group 在
     * loadValues/保存时才读取，因此实例化后到下次 save 之前作用域必须保持该租户）。
     *
     * $tenant 传 null 时作用于中央上下文（无租户数据，返回默认值）。
     */
    public function resolveTenantSettings(Module $module, ?Tenant $tenant = null): ?ModuleTenantSettings
    {
        $class = $this->tenantSettingsClass($module);

        if (! $class) {
            return null;
        }

        app(ModuleSettingsScope::class)->setTenant($tenant?->getTenantKey());

        $settings = new $class;

        // 同名回退：未存储的字段读取模块平台设置值（未覆盖的租户跟随平台默认修改）
        $platform = $this->resolvePlatformSettings($module);

        if ($platform) {
            $settings->applyCentralFallbacks($platform->toCollection());
        }

        return $settings;
    }

    // ---------------------------------------------------------------
    // 设置数据清理
    // ---------------------------------------------------------------

    /**
     * 删除模块的设置数据（卸载时清理，保持与 modules/tenant_modules 记录的关联一致）。
     *
     * $tenant 传 null 时删除中央设置及所有租户的设置；传租户时仅删除该租户的设置。
     */
    public function deleteSettingsFor(Module $module, ?Tenant $tenant = null): void
    {
        $query = SettingsProperty::query();

        if ($tenant !== null) {
            $query->where('group', "tenant_module:{$tenant->getTenantKey()}:{$module->package_name}");

            $query->delete();

            return;
        }

        $query->where('group', "module:{$module->package_name}")
            ->orWhere('group', 'like', "tenant_module:%:{$module->package_name}")
            ->delete();
    }

    // ---------------------------------------------------------------
    // Filament 设置表单结构
    // ---------------------------------------------------------------

    /**
     * 获取模块的平台设置表单结构（供后台「模块 → 设置」页使用）。
     *
     * 表单结构由设置类自身的 schema() 方法提供，字段名与设置类 public 属性一一对应。
     *
     * @return array<int, Filament\Forms\Components\Component>
     */
    public function platformSettingsSchema(Module $module): array
    {
        $class = $this->platformSettingsClass($module);

        return $class ? $class::schema() : [];
    }

    /**
     * 获取模块的租户设置表单结构（供后台「租户 → 模块管理」页使用）。
     *
     * 表单结构由设置类自身的 schema() 方法提供，字段名与设置类 public 属性一一对应。
     *
     * @return array<int, Filament\Forms\Components\Component>
     */
    public function tenantSettingsSchema(Module $module): array
    {
        $class = $this->tenantSettingsClass($module);

        return $class ? $class::schema() : [];
    }
}
