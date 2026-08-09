<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Module;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Module\Settings\ModuleSettingsScope;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

/**
 * 模块启动加载器 —— 负责中央/租户上下文模块资源加载、模块生命周期。
 *
 * 职责：
 * - loadCentralModules()/loadTenantModules(Tenant)：上下文加载入口
 * - loadModule()/registerProvider()/loadViews()/loadConfig()：模块资源加载
 * - enable()/disable()/uninstall()/enableForTenant()/disableForTenant()/uninstallForTenant()：
 *   模块生命周期（含租户路由缓存失效）
 *
 * 中央 web 业务路由不在此类加载，统一由 CentralRouteManager::dispatchAll() 注册；
 * 租户路由不在此注册，由 InitializeTenantAndDispatchRoutes 中间件按域名分发加载。
 */
class ModuleBootLoader
{
    public function __construct(
        protected Application $app,
        protected ModuleDiscoveryManager $discovery,
        protected ModuleMigrationService $migrations,
    ) {}

    // ---------------------------------------------------------------
    // 上下文加载入口
    // ---------------------------------------------------------------

    /**
     * 在中央上下文中加载所有 central 区域的模块。
     *
     * 在 AppServiceProvider::boot() 或 ModuleServiceProvider::boot() 中调用。
     */
    public function loadCentralModules(): void
    {
        $modules = $this->discovery->sortedModules(
            $this->discovery->discover()->filter(fn (Module $module) => $this->discovery->supportsArea($module, 'central'))
        );

        foreach ($modules as $module) {
            $this->loadModule($module);
        }
    }

    /**
     * 在租户上下文中加载当前租户启用的 tenant 区域模块。
     *
     * 在 TenancyInitialized 事件监听器中调用。
     */
    public function loadTenantModules(Tenant $tenant): void
    {
        $enabledPackages = $tenant->getEnabledModules();

        if (empty($enabledPackages)) {
            return;
        }

        // 设置租户设置作用域，使模块租户设置类解析 group 时指向当前租户
        app(ModuleSettingsScope::class)->setTenant($tenant->getTenantKey());

        $modules = $this->discovery->sortedModules(
            $this->discovery->discover()
                ->filter(fn (Module $module) => $this->discovery->supportsArea($module, 'tenant'))
                ->filter(fn (Module $module) => in_array($module->package_name, $enabledPackages, true))
        );

        foreach ($modules as $module) {
            $this->loadModule($module);
        }
    }

    // ---------------------------------------------------------------
    // 单个模块加载
    // ---------------------------------------------------------------

    /**
     * 加载单个模块：注册 Provider、加载视图/配置。
     *
     * 中央 web 业务路由不在此加载（由 CentralRouteManager 统一注册），
     * provider 不存在时仅兜底加载视图与配置。
     */
    public function loadModule(Module $module): void
    {
        if ($this->discovery->isLoaded($module->package_name)) {
            return;
        }

        $this->discovery->markLoaded($module->package_name);

        // 注册并 boot ServiceProvider；provider 不存在时由本类兜底加载视图/配置
        $providers = $this->registerProvider($module);

        if (empty($providers)) {
            $this->loadViews($module);
            $this->loadConfig($module);
        }
    }

    /**
     * 注册模块的 ServiceProvider。
     *
     * @return ServiceProvider[] 成功注册的 provider 实例数组
     */
    protected function registerProvider(Module $module): array
    {
        $classes = $module->providers;

        if (empty($classes)) {
            return [];
        }

        $providers = [];

        foreach ((array) $classes as $class) {
            if (! class_exists($class)) {
                continue;
            }

            if (! is_subclass_of($class, ServiceProvider::class)) {
                continue;
            }

            /** @var ServiceProvider $provider */
            $provider = $this->app->register($class);

            // 如果 provider 已在之前的请求中被 boot，需要手动 boot
            if (method_exists($provider, 'isBooted') && ! $provider->isBooted() && $this->app->isBooted()) {
                $this->app->call([$provider, 'boot']);
            }

            $providers[] = $provider;
        }

        return $providers;
    }

    /**
     * 注册模块的视图命名空间。
     */
    protected function loadViews(Module $module): void
    {
        $viewPath = $module->path.'/resources/views';

        if (is_dir($viewPath)) {
            // 使用包名作为视图命名空间前缀
            $namespace = str_replace('/', '-', $module->package_name);

            $this->app['view']->addNamespace($namespace, $viewPath);
        }
    }

    /**
     * 加载模块配置文件。
     *
     * 仅用于无 ServiceProvider 的模块（provider 存在的模块由 provider 自行 mergeConfigFrom）。
     */
    protected function loadConfig(Module $module): void
    {
        $configPath = $module->path.'/config';

        if (! is_dir($configPath)) {
            return;
        }

        $namespace = str_replace('/', '.', $module->package_name);

        foreach (File::files($configPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $key = $file->getBasename('.php');

            $this->app['config']->set($namespace.'.'.$key, require $file->getPathname());
        }
    }

    // ---------------------------------------------------------------
    // 生命周期钩子
    // ---------------------------------------------------------------

    /**
     * 启用模块并调用其生命周期钩子。
     *
     * 首次启用（install）与后续启用都会运行 pending 迁移（幂等），
     * 因此模块升级新增的迁移文件无需额外操作即可补跑。
     */
    public function enable(Module $module): void
    {
        $isFirstInstall = ! $module->isInstalled();

        $module->update([
            'status' => 'active',
            'installed_at' => $module->installed_at ?? now(),
        ]);

        $this->discovery->flushPanelPluginsCache();

        $this->migrations->migrate($module);

        // 注册 Provider 以调用钩子
        $provider = $this->discovery->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            if ($isFirstInstall) {
                $provider->install();
            }
            $provider->onEnable();
        }

        TenantRouteCache::flushAll();
    }

    /**
     * 禁用模块并调用其生命周期钩子。
     */
    public function disable(Module $module): void
    {
        $module->update(['status' => 'inactive']);

        $provider = $this->discovery->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            $provider->onDisable();
        }

        TenantRouteCache::flushAll();
    }

    /**
     * 卸载模块：调用 uninstall() 钩子、回滚迁移，然后删除数据库记录。
     */
    public function uninstall(Module $module): void
    {
        // 先加载模块以便调用 uninstall 钩子
        $provider = $this->discovery->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            $hasEnabledTenant = TenantModule::where('module_id', $module->id)->where('enabled', true)->exists();
            // 如果还有启用该模块的租户，不执行卸载
            if ($hasEnabledTenant) {
                return;
            }

            $provider->uninstall();
        }

        // 回滚该模块全部迁移（删除其表），并清空 module_migrations 残留记录
        $this->migrations->rollback($module);
        $this->migrations->purge($module);

        app(ModuleSettingManager::class)->deleteSettingsFor($module);

        $module->delete();

        $this->discovery->flushPanelPluginsCache();

        TenantRouteCache::flushAll();
    }

    // ---------------------------------------------------------------
    // 租户侧安装/卸载
    // ---------------------------------------------------------------

    /**
     * 为指定租户安装/启用模块。
     *
     * 首次安装：创建 tenant_modules 记录、在租户库运行模块迁移并调用 tenantInstall()；
     * 重复启用：仅把 enabled 置为 true 并调用 tenantOnEnable()（迁移幂等，新增文件自动补跑）。
     */
    public function enableForTenant(Module $module, Tenant $tenant): void
    {
        $isFirstInstall = ! $tenant->tenantModules()
            ->where('module_id', $module->id)
            ->exists();

        // 记录写入中央库，必须在初始化租户上下文之前完成
        $tenant->setModuleEnabled($module->id, true);

        // 在租户库运行模块 pending 迁移（首次安装全部，升级补跑新增）
        $this->migrations->migrateForTenant($module, $tenant);

        $provider = $this->discovery->resolveModuleProvider($module);

        if (! $provider instanceof ModuleServiceProvider) {
            return;
        }

        if ($isFirstInstall) {
            $provider->tenantInstall($tenant);
        }

        $provider->tenantOnEnable($tenant);

        TenantRouteCache::flushTenant($tenant);
    }

    /**
     * 禁用指定租户的模块（enabled 置为 false），模块租户功能不再加载。
     */
    public function disableForTenant(Module $module, Tenant $tenant): void
    {
        $tenant->setModuleEnabled($module->id, false);

        $provider = $this->discovery->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            $provider->tenantOnDisable($tenant);
        }

        TenantRouteCache::flushTenant($tenant);
    }

    /**
     * 从指定租户卸载模块：调用 tenantUninstall() 钩子、回滚租户库迁移，然后删除记录。
     */
    public function uninstallForTenant(Module $module, Tenant $tenant): void
    {
        $provider = $this->discovery->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            $provider->tenantUninstall($tenant);
        }

        // 回滚该模块在该租户库的全部迁移，并清空 module_migrations 残留记录
        $this->migrations->rollbackForTenant($module, $tenant);
        $this->migrations->purgeForTenant($module, $tenant);

        $tenant->tenantModules()->where('module_id', $module->id)->delete();

        app(ModuleSettingManager::class)->deleteSettingsFor($module, $tenant);

        TenantRouteCache::flushTenant($tenant);
    }
}
