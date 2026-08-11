<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Module;
use App\Module\Contracts\AdminPanelPlugin;
use App\Module\Contracts\TenantAdminPanelPlugin;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

/**
 * 模块元数据发现管理器 —— 只负责模块信息查询、排序、解析。
 *
 * 不做任何资源加载：不注册 ServiceProvider、不加载迁移/视图/配置、
 * 不加载路由文件、不处理模块生命周期、不处理 Spatie Settings。
 *
 * 职责：
 * - discover()/flushCache()：数据库 active 模块发现缓存
 * - sortedModules()：依赖/after/weight 拓扑排序
 * - supportsArea()/resolveModulePath()：基础解析
 * - getAdminPanelPlugins()/getTenantAdminPanelPlugins()：Filament 插件按约定发现
 * - resolveModuleProvider()：静态实例化模块 ServiceProvider（仅实例化，不 boot）
 */
class ModuleDiscoveryManager
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
     * 清除发现缓存与已加载集合，强制重新发现。
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

    /**
     * 标记模块已加载（由 ModuleBootLoader::loadModule() 调用）。
     */
    public function markLoaded(string $packageName): void
    {
        $this->loaded[] = $packageName;
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
    // 基础解析
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
    // Filament 面板插件发现
    // ---------------------------------------------------------------

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
        try {
            return Cache::rememberForever(
                "lasaas.{$cacheKey}",
                fn (): array => $this->resolvePanelPlugins($interface, $filter),
            );
        } catch (\Throwable) {
            // 缓存后端不可用（如 cache 表尚未迁移、数据库瞬断），降级为不缓存直接计算
            return $this->resolvePanelPlugins($interface, $filter);
        }
    }

    /**
     * 计算实现指定接口的插件类列表（不做缓存）。
     *
     * @return array<class-string>
     */
    protected function resolvePanelPlugins(string $interface, Closure $filter): array
    {
        $plugins = [];

        foreach ($this->discover()->filter($filter) as $module) {
            foreach ($this->findPanelPluginClasses($module) as $pluginClass) {
                if (class_exists($pluginClass) && is_subclass_of($pluginClass, $interface)) {
                    $plugins[] = $pluginClass;
                }
            }
        }

        return array_values(array_unique($plugins));
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

    // ---------------------------------------------------------------
    // Provider 静态解析
    // ---------------------------------------------------------------

    /**
     * 解析模块的 ServiceProvider 实例（不注册，仅用于调用钩子）。
     *
     * 注意：不能用 make() 实例化——ServiceProvider 的构造参数没有类型提示，
     * 容器无法自动注入，需要手动传入 app 实例。
     */
    public function resolveModuleProvider(Module $module): ?ServiceProvider
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
}
