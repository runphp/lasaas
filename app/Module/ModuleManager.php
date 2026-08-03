<?php

declare(strict_types=1);

namespace App\Module;

use App\Http\Middleware\EnsureTenantAccessible;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Module\Contracts\AdminPanelPlugin;
use App\Module\Contracts\TenantAdminPanelPlugin;
use App\Module\Settings\ModulePlatformSettings;
use App\Module\Settings\ModuleSettingsScope;
use App\Module\Settings\ModuleTenantSettings;
use Closure;
use Filament\Contracts\Plugin;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/**
 * 模块管理器 —— 负责模块发现、排序和加载。
 *
 * 模块加载流程：
 * 1. discover()——从数据库和磁盘发现已安装模块
 * 2. sortedModules()——按依赖/after/weight 拓扑排序
 * 3. loadCentralModules()——在中央上下文加载 central 区域的模块
 * 4. loadTenantModules(Tenant)——在租户上下文加载租户启用且 tenant 区域的模块
 *
 * 加载一个模块意味着：
 * - 注册并 boot 其 ServiceProvider
 * - 加载其 migrations、routes、views、config
 */
class ModuleManager
{
    /** 已发现模块缓存 */
    protected ?Collection $modules = null;

    /** 已加载的模块包名集合（防重复加载） */
    protected array $loaded = [];

    public function __construct(
        protected Application $app,
    ) {}

    // ---------------------------------------------------------------
    // 发现
    // ---------------------------------------------------------------

    /**
     * 从数据库获取所有 active 状态的模块。
     *
     * @return Collection<int, Module>
     */
    public function discover(): Collection
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        try {
            $this->modules = Module::where('status', 'active')
                ->orderBy('weight')
                ->get()
                ->keyBy('package_name');
        } catch (\Throwable) {
            // 数据库可能尚未迁移（首次部署、测试环境等），返回空集合
            $this->modules = collect();
        }

        return $this->modules;
    }

    /**
     * 清除缓存，强制重新发现。
     */
    public function flushCache(): void
    {
        $this->modules = null;
        $this->loaded = [];
    }

    /**
     * 判断模块是否已加载。
     */
    public function isLoaded(string $packageName): bool
    {
        return in_array($packageName, $this->loaded, true);
    }

    /**
     * 获取已加载模块列表。
     *
     * @return string[]
     */
    public function getLoaded(): array
    {
        return $this->loaded;
    }

    // ---------------------------------------------------------------
    // 排序
    // ---------------------------------------------------------------

    /**
     * 拓扑排序，返回按加载顺序排列的模块。
     *
     * 排序规则：
     * 1. dependencies——强依赖，必须在依赖模块之后加载
     * 2. after——非强依赖但必须排在指定模块之后
     * 3. weight——同级内从小到大排序
     *
     * @param  Collection<int, Module>  $modules
     * @return Collection<int, Module>
     */
    public function sortedModules(?Collection $modules = null): Collection
    {
        $modules ??= $this->discover();

        if ($modules->isEmpty()) {
            return $modules;
        }

        $packageNames = $modules->keys()->all();
        $sorted = [];
        $visited = [];

        // DFS 拓扑排序
        foreach ($packageNames as $name) {
            $this->topologicalVisit($name, $modules, $visited, $sorted);
        }

        return collect($sorted);
    }

    /**
     * DFS 访问节点。
     */
    protected function topologicalVisit(string $name, Collection $modules, array &$visited, array &$sorted): void
    {
        if (isset($visited[$name])) {
            if ($visited[$name] === 'visiting') {
                // 检测到循环依赖，记录警告但不中断
                report(new \RuntimeException("Circular dependency detected for module: {$name}"));

                return;
            }

            return; // 已处理过
        }

        $visited[$name] = 'visiting';

        $module = $modules->get($name);
        if (! $module) {
            $visited[$name] = 'visited';

            return;
        }

        // 先处理强依赖 (dependencies)
        $deps = $module->dependencies ?? [];
        foreach ($deps as $dep) {
            if ($modules->has($dep)) {
                $this->topologicalVisit($dep, $modules, $visited, $sorted);
            }
        }

        // 再处理 after 列表
        $afters = $module->after ?? [];
        foreach ($afters as $after) {
            if ($modules->has($after)) {
                $this->topologicalVisit($after, $modules, $visited, $sorted);
            }
        }

        $visited[$name] = 'visited';
        $sorted[] = $module;
    }

    // ---------------------------------------------------------------
    // 加载 —— 中央上下文
    // ---------------------------------------------------------------

    /**
     * 在中央上下文中加载所有 central 区域的模块。
     *
     * 在 AppServiceProvider::boot() 或 ModuleServiceProvider::boot() 中调用。
     */
    public function loadCentralModules(): void
    {
        $modules = $this->sortedModules(
            $this->discover()->filter(fn (Module $m) => $this->supportsArea($m, 'central'))
        );

        foreach ($modules as $module) {
            $this->loadModule($module);
        }

        $this->registerTenantModuleRoutes();
    }

    /**
     * 在中央引导阶段注册所有 tenant 区域模块的租户路由。
     *
     * 租户路由必须在 app boot 时注册（与框架自身 routes/tenant.php 同一约定），
     * 因为路由匹配发生在 tenancy 初始化之前——若等到 TenancyInitialized 事件
     * 加载模块时才注册路由，请求将因找不到路由而 404。
     *
     * 路由组使用与框架租户路由一致的中间件：先按域名初始化租户上下文，
     * 再执行 web 中间件，最后拦截中央域名的访问。
     */
    public function registerTenantModuleRoutes(): void
    {
        $modules = $this->discover()->filter(fn (Module $m) => $this->supportsArea($m, 'tenant'));

        foreach ($modules as $module) {
            $path = $module->path.'/routes/tenant.php';

            if (! file_exists($path)) {
                continue;
            }

            Route::middleware([
                InitializeTenancyByDomain::class,
                EnsureTenantAccessible::class,
                'web',
                PreventAccessFromCentralDomains::class,
            ])->group($path);
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

        $modules = $this->sortedModules(
            $this->discover()
                ->filter(fn (Module $m) => $this->supportsArea($m, 'tenant'))
                ->filter(fn (Module $m) => in_array($m->package_name, $enabledPackages, true))
        );

        foreach ($modules as $module) {
            $this->loadModule($module);
        }
    }

    // ---------------------------------------------------------------
    // 单个模块加载
    // ---------------------------------------------------------------

    /**
     * 加载单个模块：注册 Provider、加载迁移/路由/视图/配置。
     */
    public function loadModule(Module $module): void
    {
        if ($this->isLoaded($module->package_name)) {
            return;
        }

        $this->loaded[] = $module->package_name;

        // 第一步：注册并 boot ServiceProvider
        $providers = $this->registerProvider($module);

        // 第二步：加载模块资源（provider 不存在时由 ModuleManager 兜底加载）
        if (empty($providers)) {
            $this->loadMigrations($module);
            $this->loadRoutes($module);
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
     * 加载模块的数据库迁移文件。
     */
    protected function loadMigrations(Module $module): void
    {
        $migrationPath = $module->path.'/database/migrations';

        if (is_dir($migrationPath)) {
            $this->app['migrator']->path($migrationPath);
        }
    }

    /**
     * 加载模块的中央应用路由文件。
     *
     * 与框架约定一致：模块只需 routes/web.php（中央应用）与 routes/tenant.php（租户）。
     * web.php 只对 central 区域模块加载；tenant.php 由 registerTenantModuleRoutes()
     * 在 app boot 阶段以租户中间件组注册，这里不重复加载。
     */
    protected function loadRoutes(Module $module): void
    {
        if (! in_array('central', $module->areas ?? [], true)) {
            return;
        }

        $path = $module->path.'/routes/web.php';

        if (file_exists($path)) {
            require $path;
        }
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
    // 设置（Settings）
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
        $provider = $this->resolveModuleProvider($module);

        return $provider instanceof ModuleServiceProvider ? $provider->settingsClasses() : [];
    }

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
     * $tenant 传 null 时作用于中央上下文（无租户数据，返回默认值）。
     *
     * spatie 的 group() 在懒加载/保存时才动态读取，而租户 key 来自共享的可变作用域，
     * 同一请求内多次解析不同租户会把作用域互相覆盖，导致实例错读其他租户的分组。
     * 因此这里为每个 (设置类, 租户) 生成一个 group() 固定为该租户分组的设置子类，
     * 使实例完全按租户隔离。
     */
    public function resolveTenantSettings(Module $module, ?Tenant $tenant = null): ?ModuleTenantSettings
    {
        $class = $this->tenantSettingsClass($module);

        if (! $class) {
            return null;
        }

        $group = 'tenant_module:'.($tenant?->getTenantKey() ?? 'central').':'.$class::groupKey();

        $settingsClass = $this->generateTenantSettingsClass($class, $group);

        $settings = new $settingsClass;

        // 同名回退：未存储的字段读取模块平台设置值（未覆盖的租户跟随平台默认修改）
        $platform = $this->resolvePlatformSettings($module);

        if ($platform) {
            $settings->applyCentralFallbacks($platform->toCollection());
        }

        return $settings;
    }

    /**
     * 生成按 (设置类, 分组) 缓存的租户设置子类，其 group() 固定返回给定分组。
     *
     * PHP 匿名类无法继承变量指定的基类，因此用 eval 声明一个确定命名的派生类
     * （基类名 + 分组的哈希后缀），类名确定且按 (基类, 分组) 缓存，避免重复声明。
     */
    protected function generateTenantSettingsClass(string $baseClass, string $group): string
    {
        static $classes = [];

        $cacheKey = $baseClass.'|'.$group;

        if (isset($classes[$cacheKey])) {
            return $classes[$cacheKey];
        }

        $name = class_basename($baseClass).'ForTenant'.substr(md5($baseClass.'|'.$group), 0, 16);
        $extends = '\\'.ltrim($baseClass, '\\');
        $groupLiteral = var_export($group, true);

        if (! class_exists($name)) {
            eval(<<<PHP
            class {$name} extends {$extends}
            {
                public static function group(): string
                {
                    return {$groupLiteral};
                }
            }
            PHP);
        }

        return $classes[$cacheKey] = $name;
    }

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

    // ---------------------------------------------------------------
    // 辅助方法
    // ---------------------------------------------------------------

    /**
     * 判断模块是否支持指定区域。
     */
    public function supportsArea(Module $module, string $area): bool
    {
        $areas = $module->areas ?? [];

        if (empty($areas)) {
            return $area === 'central'; // 默认仅 central
        }

        return in_array($area, $areas, true);
    }

    /**
     * 获取模块磁盘路径（基于包名推导），不依赖数据库。
     *
     * 优先级：packages/custom/ > packages/contrib/
     */
    public function resolveModulePath(string $packageName): string
    {
        $customPath = base_path('packages/custom/'.$packageName);

        if (is_dir($customPath)) {
            return $customPath;
        }

        return base_path('packages/contrib/'.$packageName);
    }

    // ---------------------------------------------------------------
    // 生命周期钩子
    // ---------------------------------------------------------------

    /**
     * 启用模块并调用其生命周期钩子。
     */
    public function enable(Module $module): void
    {
        $isFirstInstall = ! $module->isInstalled();

        $module->update([
            'status' => 'active',
            'installed_at' => $module->installed_at ?? now(),
        ]);

        $this->flushPanelPluginsCache();

        // 注册 Provider 以调用钩子
        $provider = $this->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            if ($isFirstInstall) {
                $provider->install();
            }
            $provider->onEnable();
        }
    }

    /**
     * 禁用模块并调用其生命周期钩子。
     */
    public function disable(Module $module): void
    {
        $module->update(['status' => 'inactive']);

        $provider = $this->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            $provider->onDisable();
        }
    }

    /**
     * 卸载模块：调用 uninstall() 钩子，然后删除数据库记录。
     */
    public function uninstall(Module $module): void
    {
        // 先加载模块以便调用 uninstall 钩子
        $provider = $this->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            $hasEnabledTenant = TenantModule::where('module_id', $module->id)->where('enabled', true)->exists();
            // 如果还有启用该模块的租户，不执行卸载
            if ($hasEnabledTenant) {
                return;
            }

            $provider->uninstall();
        }

        $this->deleteSettingsFor($module);

        $module->delete();

        $this->flushPanelPluginsCache();
    }

    // ---------------------------------------------------------------
    // 租户侧安装/卸载
    // ---------------------------------------------------------------

    /**
     * 为指定租户安装/启用模块。
     *
     * 首次安装：创建 tenant_modules 记录、在租户库运行模块迁移并调用 tenantInstall()；
     * 重复启用：仅把 enabled 置为 true 并调用 tenantOnEnable()。
     */
    public function enableForTenant(Module $module, Tenant $tenant): void
    {
        $isFirstInstall = ! $tenant->tenantModules()
            ->where('module_id', $module->id)
            ->exists();

        // 记录写入中央库，必须在初始化租户上下文之前完成
        $tenant->setModuleEnabled($module->id, true);

        $provider = $this->resolveModuleProvider($module);

        if (! $provider instanceof ModuleServiceProvider) {
            return;
        }

        if ($isFirstInstall) {
            $this->withTenancy($tenant, fn () => $provider->tenantInstall($tenant));
        }

        $provider->tenantOnEnable($tenant);
    }

    /**
     * 禁用指定租户的模块（enabled 置为 false），模块租户功能不再加载。
     */
    public function disableForTenant(Module $module, Tenant $tenant): void
    {
        $tenant->setModuleEnabled($module->id, false);

        $provider = $this->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            $provider->tenantOnDisable($tenant);
        }
    }

    /**
     * 从指定租户卸载模块：调用 tenantUninstall() 钩子（回滚租户库迁移等），然后删除记录。
     */
    public function uninstallForTenant(Module $module, Tenant $tenant): void
    {
        $provider = $this->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            $this->withTenancy($tenant, fn () => $provider->tenantUninstall($tenant));
        }

        $tenant->tenantModules()->where('module_id', $module->id)->delete();

        $this->deleteSettingsFor($module, $tenant);
    }

    /**
     * 在租户上下文中执行回调，结束后恢复中央上下文。
     */
    protected function withTenancy(Tenant $tenant, Closure $callback): void
    {
        if (tenancy()->initialized) {
            $callback();

            return;
        }

        tenancy()->initialize($tenant);

        try {
            $callback();
        } finally {
            tenancy()->end();
        }
    }

    /**
     * 解析模块的 ServiceProvider 实例（不注册，仅用于调用钩子）。
     *
     * 注意：不能用 make() 实例化——ServiceProvider 的构造参数没有类型提示，
     * 容器无法自动注入，需要手动传入 app 实例。
     */
    protected function resolveModuleProvider(Module $module): ?ServiceProvider
    {
        $providers = $module->providers;

        // 数组为空直接返回
        if (empty($providers)) {
            return null;
        }

        // 取第一个为主服务提供者
        $class = $providers[0];

        // 类不存在
        if (! class_exists($class)) {
            return null;
        }

        // 必须继承 ServiceProvider
        if (! is_subclass_of($class, ServiceProvider::class)) {
            return null;
        }

        // 实例化并返回
        return new $class($this->app);
    }

    /**
     * 获取所有 central 区域模块的中央 admin 面板插件类名列表。
     *
     * 约定（约定大于配置）：扫描模块 PSR-4 根目录下 Filament/Plugins 子目录中
     * 实现 AdminPanelPlugin 接口的类，无需在 composer.json 中声明。
     *
     * @return array<class-string<AdminPanelPlugin>>
     */
    public function getAdminPanelPlugins(): array
    {
        return $this->discoverPanelPlugins(
            AdminPanelPlugin::class,
            'admin_panel_plugins',
            fn (Module $module): bool => $this->supportsArea($module, 'central'),
        );
    }

    /**
     * 获取所有支持 tenant 区域的模块的租户 admin 面板插件类名列表。
     *
     * 约定（约定大于配置）：扫描模块 PSR-4 根目录下 Filament/Plugins 子目录中
     * 实现 TenantAdminPanelPlugin 接口的类，无需在 composer.json 中声明。
     *
     * @return array<class-string<TenantAdminPanelPlugin>>
     */
    public function getTenantAdminPanelPlugins(): array
    {
        return $this->discoverPanelPlugins(
            TenantAdminPanelPlugin::class,
            'tenant_admin_panel_plugins',
            fn (Module $module): bool => $this->supportsArea($module, 'tenant'),
        );
    }

    /**
     * 清除面板插件发现缓存，模块启用/禁用/卸载及 module:sync 后调用。
     */
    public function flushPanelPluginsCache(): void
    {
        Cache::forget('lasaas.admin_panel_plugins');
        Cache::forget('lasaas.tenant_admin_panel_plugins');
    }

    /**
     * 按约定发现指定面板插件接口的实现类。
     *
     * @param  class-string  $interface
     * @param  Closure(Module): bool  $filter
     * @return array<class-string>
     */
    protected function discoverPanelPlugins(string $interface, string $cacheKey, Closure $filter): array
    {
        return Cache::rememberForever("lasaas.{$cacheKey}", function () use ($interface, $filter): array {
            $plugins = [];

            foreach ($this->discover()->filter($filter) as $module) {
                foreach ($this->findPanelPluginClasses($module) as $pluginClass) {
                    if (class_exists($pluginClass) && is_subclass_of($pluginClass, $interface)) {
                        $plugins[] = $pluginClass;
                    }
                }
            }

            return array_values(array_unique($plugins));
        });
    }

    /**
     * 从模块的 PSR-4 根目录下 Filament/Plugins 子目录解析候选插件类名。
     *
     * @return array<class-string>
     */
    protected function findPanelPluginClasses(Module $module): array
    {
        $composerPath = $module->path.'/composer.json';

        if (! file_exists($composerPath)) {
            return [];
        }

        $composerJson = json_decode(file_get_contents($composerPath), true);

        if (! is_array($composerJson)) {
            return [];
        }

        $classes = [];

        foreach ($composerJson['autoload']['psr-4'] ?? [] as $namespace => $srcDir) {
            $basePath = $module->path.'/'.rtrim(is_array($srcDir) ? $srcDir[0] : $srcDir, '/');

            if (! is_dir($basePath)) {
                continue;
            }

            $pluginsDir = rtrim($basePath, '/').'/Filament/Plugins';

            if (! is_dir($pluginsDir)) {
                continue;
            }

            foreach (File::allFiles($pluginsDir) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relative = substr($file->getPathname(), strlen(rtrim($basePath, '/')) + 1, -4);
                $classes[] = $namespace.str_replace('/', '\\', $relative);
            }
        }

        return $classes;
    }

    /**
     * 获取所有 central 区域模块中声明的 Filament Plugin 类名列表。
     *
     * @deprecated 使用 getAdminPanelPlugins() 按约定发现
     *
     * @return array<class-string<Plugin>>
     */
    public function getCentralFilamentPlugins(): array
    {
        return $this->getAdminPanelPlugins();
    }
}
