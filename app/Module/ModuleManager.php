<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Module;
use App\Models\Tenant;
use Filament\Contracts\Plugin;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

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

        $this->modules = Module::where('status', 'active')
            ->orderBy('weight')
            ->get()
            ->keyBy('package_name');

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

            // 合并租户级别配置覆盖（模块默认 + 中央覆盖之上）
            $this->mergeTenantConfig($tenant, $module);
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
        }
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
            'tenant' => $module->path.'/routes/tenant.php',
            'web' => $module->path.'/routes/web.php',
            'api' => $module->path.'/routes/api.php',
        ];

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

            // tenant 路由只对 tenant 区域模块加载
            if ($type === 'tenant' && ! in_array('tenant', $areas, true)) {
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
     * 租户覆盖在 loadTenantModules() 中额外合并（租户访问时才有）。
     */
    protected function loadConfig(Module $module): void
    {
        $configPath = $module->path.'/config';

        if (! is_dir($configPath)) {
            return;
        }

        $namespace = str_replace('/', '.', $module->package_name);
        $centralOverrides = $module->config ?? [];

        foreach (File::files($configPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $key = $file->getBasename('.php');
            $configKey = $namespace.'.'.$key;

            // 1. 模块默认配置
            $config = require $file->getPathname();

            // 2. 中央级别覆盖 (modules.config)
            if (isset($centralOverrides[$key]) && is_array($centralOverrides[$key])) {
                $config = array_replace_recursive($config, $centralOverrides[$key]);
            }

            $this->app['config']->set($configKey, $config);
        }
    }

    /**
     * 合并租户级别配置覆盖（仅租户上下文调用）。
     *
     * 在模块默认 + 中央覆盖已加载的基础上，用 tenant_modules.settings 覆盖。
     */
    protected function mergeTenantConfig(Tenant $tenant, Module $module): void
    {
        $tenantModule = $tenant->tenantModules()
            ->where('module_id', $module->id)
            ->first();

        if (! $tenantModule || empty($tenantModule->settings)) {
            return;
        }

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

        Cache::forget('lasaas.central_filament_plugins');

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
            $provider->uninstall();
        }

        $module->delete();

        Cache::forget('lasaas.central_filament_plugins');
    }

    /**
     * 解析模块的 ServiceProvider 实例（不注册，仅用于调用钩子）。
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

        return $this->app->make($class);
    }

    /**
     * 获取所有 central 区域模块中声明的 Filament Plugin 类名列表。
     *
     * 结果会被缓存，module:sync、enable、uninstall 操作会清除缓存。
     *
     * @return array<class-string<Plugin>>
     */
    public function getCentralFilamentPlugins(): array
    {
        return Cache::rememberForever('lasaas.central_filament_plugins', function (): array {
            $plugins = [];

            $centralModules = $this->discover()
                ->filter(fn (Module $m) => $this->supportsArea($m, 'central'));

            foreach ($centralModules as $module) {
                $composerPath = $module->path.'/composer.json';

                if (! file_exists($composerPath)) {
                    continue;
                }

                $composerJson = json_decode(file_get_contents($composerPath), true);

                if (! is_array($composerJson)) {
                    continue;
                }

                $pluginClasses = $composerJson['extra']['lasaas-module']['plugins'] ?? [];

                if (is_string($pluginClasses)) {
                    $pluginClasses = [$pluginClasses];
                }

                foreach ($pluginClasses as $pluginClass) {
                    if (class_exists($pluginClass) && is_subclass_of($pluginClass, Plugin::class)) {
                        $plugins[] = $pluginClass;
                    }
                }
            }

            return $plugins;
        });
    }
}
