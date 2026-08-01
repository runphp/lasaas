<?php

declare(strict_types=1);

namespace App\Module;

use App\Http\Middleware\EnsureTenantAccessible;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Module\Contracts\AdminPanelPlugin;
use App\Module\Contracts\TenantAdminPanelPlugin;
use Closure;
use Filament\Contracts\Plugin;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
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

        $modules = $this->sortedModules(
            $this->discover()
                ->filter(fn (Module $m) => $this->supportsArea($m, 'tenant'))
                ->filter(fn (Module $m) => in_array($m->package_name, $enabledPackages, true))
        );

        foreach ($modules as $module) {
            $this->loadModule($module);

            // 合并租户级别配置覆盖（模块默认 + 中央覆盖之上）
            $this->mergeTenantConfig($tenant, $module);
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
        $provider = $this->registerProvider($module);

        // 第二步：加载模块资源（provider 不存在时由 ModuleManager 兜底加载）
        if ($provider === null) {
            $this->loadMigrations($module);
            $this->loadRoutes($module);
            $this->loadViews($module);
            $this->loadConfig($module);

            return;
        }

        // 第三步：合并中央级别设置（schema 默认配置之上，覆盖 modules.settings）
        $this->mergeCentralSettings($module, $provider);
    }

    /**
     * 注册模块的 ServiceProvider。
     *
     * @return ServiceProvider|null 成功注册的 provider 实例
     */
    protected function registerProvider(Module $module): ?ServiceProvider
    {
        $class = $module->provider_class;

        if (! class_exists($class)) {
            return null;
        }

        if (! is_subclass_of($class, ServiceProvider::class)) {
            return null;
        }

        /** @var ServiceProvider $provider */
        $provider = $this->app->register($class);

        // 如果 provider 已在之前的请求中被 boot，需要手动 boot
        if (method_exists($provider, 'isBooted') && ! $provider->isBooted() && $this->app->isBooted()) {
            $this->app->call([$provider, 'boot']);
        }

        return $provider;
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
     * 加载模块的路由文件。
     */
    protected function loadRoutes(Module $module): void
    {
        $routeFiles = [
            'central' => $module->path.'/routes/central.php',
            'web' => $module->path.'/routes/web.php',
            'api' => $module->path.'/routes/api.php',
        ];

        // 注意：tenant.php 不在其中——租户路由统一由 registerTenantModuleRoutes()
        // 在 app boot 阶段以租户中间件组注册，这里不再重复加载。

        // 根据模块支持的 area 决定加载哪些路由
        $areas = $module->areas ?? [];

        foreach ($routeFiles as $type => $path) {
            if (! file_exists($path)) {
                continue;
            }

            // central 路由只对 central 区域模块加载
            if ($type === 'central' && ! in_array('central', $areas, true)) {
                continue;
            }

            // web 路由属于中央应用上下文，只对 central 区域模块加载
            if ($type === 'web' && ! in_array('central', $areas, true)) {
                continue;
            }

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
     * 加载模块配置文件，按优先级合并：模块默认 → 中央覆盖。
     *
     * 仅用于无 ServiceProvider 的模块（provider 存在的模块由 provider 自行 mergeConfigFrom，
     * 中央设置经 mergeCentralSettings() 合并）。
     */
    protected function loadConfig(Module $module): void
    {
        $configPath = $module->path.'/config';

        if (! is_dir($configPath)) {
            return;
        }

        $namespace = str_replace('/', '.', $module->package_name);
        $centralOverrides = $module->settings ?? [];

        foreach (File::files($configPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $key = $file->getBasename('.php');
            $configKey = $namespace.'.'.$key;

            // 1. 模块默认配置
            $config = require $file->getPathname();

            // 2. 中央级别覆盖 (modules.settings)
            if (isset($centralOverrides[$key]) && is_array($centralOverrides[$key])) {
                $config = array_replace_recursive($config, $centralOverrides[$key]);
            }

            $this->app['config']->set($configKey, $config);
        }
    }

    /**
     * 把模块的中央设置合并到 provider 声明的 configKey 上。
     *
     * 默认配置优先取两个设置 schema 中声明的 default（模块无需再提供 config 文件），
     * 再叠加 modules.settings 的中央覆盖值。
     */
    protected function mergeCentralSettings(Module $module, ModuleServiceProvider $provider): void
    {
        $configKey = $provider->configKey();

        if ($configKey === null) {
            return;
        }

        $defaults = $this->settingsDefaultsFromSchemas($provider);

        if (! empty($defaults)) {
            $current = $this->app['config']->get($configKey, []);

            $this->app['config']->set($configKey, array_replace_recursive($current, $defaults));
        }

        if (empty($module->settings)) {
            return;
        }

        $this->mergeSettingsIntoConfig($configKey, $module->settings);
    }

    /**
     * 从模块 provider 声明的两个设置 schema 提取默认值，作为模块默认配置。
     *
     * 中央 schema 的默认值优先；租户 schema 只为中央 schema 未声明的 key 提供默认值。
     *
     * @return array<string, mixed>
     */
    protected function settingsDefaultsFromSchemas(ModuleServiceProvider $provider): array
    {
        $defaults = [];

        foreach ([
            $provider->centralSettingsSchema(),
            $provider->tenantSettingsSchema(),
        ] as $schema) {
            foreach ($schema as $component) {
                $name = $component->getName();

                if (array_key_exists($name, $defaults)) {
                    continue;
                }

                $default = $component->getDefaultState();

                if ($default !== null) {
                    $defaults[$name] = $default;
                }
            }
        }

        return $defaults;
    }

    /**
     * 合并租户级别设置（仅租户上下文调用）。
     *
     * 在模块默认 + 中央设置已生效的基础上，用 tenant_modules.settings 覆盖。
     */
    protected function mergeTenantConfig(Tenant $tenant, Module $module): void
    {
        $tenantModule = $tenant->tenantModules()
            ->where('module_id', $module->id)
            ->first();

        if (! $tenantModule || empty($tenantModule->settings)) {
            return;
        }

        $provider = $this->resolveModuleProvider($module);

        if ($provider instanceof ModuleServiceProvider) {
            $configKey = $provider->configKey();

            if ($configKey === null) {
                return;
            }

            $this->mergeSettingsIntoConfig($configKey, $tenantModule->settings);

            return;
        }

        // provider 不存在时的兜底：settings 按配置文件 key 组织，逐文件合并
        $namespace = str_replace('/', '.', $module->package_name);

        foreach ($tenantModule->settings as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            $configKey = $namespace.'.'.$key;
            $current = $this->app['config']->get($configKey, []);

            $this->app['config']->set($configKey, array_replace_recursive($current, $value));
        }
    }

    /**
     * 把设置数组递归合并进指定配置 key（覆盖已加载的模块默认配置）。
     */
    protected function mergeSettingsIntoConfig(string $configKey, array $settings): void
    {
        $current = $this->app['config']->get($configKey, []);

        $this->app['config']->set($configKey, array_replace_recursive($current, $settings));
    }

    /**
     * 获取模块的中央设置表单结构（供后台「模块 → 设置」页使用）。
     *
     * @return array<int, Filament\Forms\Components\Component>
     */
    public function centralSettingsSchema(Module $module): array
    {
        $provider = $this->resolveModuleProvider($module);

        return $provider instanceof ModuleServiceProvider ? $provider->centralSettingsSchema() : [];
    }

    /**
     * 获取模块的租户设置表单结构（供后台「租户 → 模块管理」页使用）。
     *
     * @return array<int, Filament\Forms\Components\Component>
     */
    public function tenantSettingsSchema(Module $module): array
    {
        $provider = $this->resolveModuleProvider($module);

        return $provider instanceof ModuleServiceProvider ? $provider->tenantSettingsSchema() : [];
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
        $class = $module->provider_class;

        if (! $class || ! class_exists($class)) {
            return null;
        }

        if (! is_subclass_of($class, ServiceProvider::class)) {
            return null;
        }

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
