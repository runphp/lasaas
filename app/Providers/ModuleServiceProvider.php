<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Module as ModuleModel;
use App\Models\Tenant;
use App\Module\Http\Middleware\EnsureModuleEnabled;
use App\Module\ModuleManager;
use App\Module\ModuleServiceProvider as ModuleBaseProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenancyEnded;
use Stancl\Tenancy\Events\TenancyInitialized;

/**
 * 模块系统集成提供者。
 *
 * 负责：
 * 1. 注册 ModuleManager 单例
 * 2. 在中央上下文 boot 时加载 central 区域模块
 * 3. 在租户初始化时加载 tenant 区域模块
 * 4. 在租户结束时卸载 tenant 模块
 */
class ModuleServiceProvider extends ServiceProvider
{
    /**
     * 注册服务。
     */
    public function register(): void
    {
        $this->app->singleton(ModuleManager::class, fn ($app) => new ModuleManager($app));

        // 加载模块 autoload 缓存文件（由 module:sync 命令生成）
        // 参考 Drupal：autoload 是构建产物，不依赖运行时扫描
        $autoloadFile = $this->app->bootstrapPath('cache/lasaas_modules_autoload.php');
        if (file_exists($autoloadFile)) {
            require $autoloadFile;
        }

        // 合并模块配置文件，使 php artisan config:cache 能收集到模块配置。
        // 仅在 config 未缓存时执行：
        //   - config:cache 命令：此时未缓存，mergeConfigFrom 注册后，config->all() 会将其写入缓存文件
        //   - 开发模式无缓存：由 ModuleManager::loadConfig() 在 boot 阶段兜底
        //   - 生产环境有缓存：配置已在缓存中，无需重复注册
        if (! $this->app->configurationIsCached()) {
            $this->mergeModuleConfigs();
        }
    }

    /**
     * 扫描所有模块包，使用 mergeConfigFrom 注册其配置文件。
     *
     * 这确保 php artisan config:cache 生成缓存文件时，模块配置不会被遗漏。
     * 正常请求中，若 config 已缓存，则跳过此步骤（值已在缓存中）；
     * 若 config 未缓存（开发模式），ModuleManager::loadConfig() 在 boot 阶段会兜底。
     */
    protected function mergeModuleConfigs(): void
    {
        $scanDirs = [
            base_path('packages/contrib'),
            base_path('packages/custom'),
        ];

        // 收集所有模块包名，用于批量查询中央覆盖配置
        $packageNames = [];
        $seen = [];

        foreach ($scanDirs as $basePath) {
            if (! is_dir($basePath)) {
                continue;
            }

            $vendorDirs = File::directories($basePath);

            foreach ($vendorDirs as $vendorDir) {
                $packageDirs = File::directories($vendorDir);

                foreach ($packageDirs as $packageDir) {
                    $composerJsonPath = $packageDir.'/composer.json';

                    if (! file_exists($composerJsonPath)) {
                        continue;
                    }

                    $composerJson = json_decode(file_get_contents($composerJsonPath), true);

                    if (! is_array($composerJson)) {
                        continue;
                    }

                    if (($composerJson['type'] ?? '') !== 'lasaas-module') {
                        continue;
                    }

                    $packageName = $composerJson['name'] ?? basename(dirname($packageDir)).'/'.basename($packageDir);

                    if (isset($seen[$packageName])) {
                        continue;
                    }
                    $seen[$packageName] = true;
                    $packageNames[] = $packageName;

                    $configPath = $packageDir.'/config';
                    if (! is_dir($configPath)) {
                        continue;
                    }

                    $namespace = str_replace('/', '.', $packageName);

                    foreach (File::files($configPath) as $file) {
                        if ($file->getExtension() !== 'php') {
                            continue;
                        }

                        $key = $namespace.'.'.$file->getBasename('.php');
                        $this->mergeConfigFrom($file->getPathname(), $key);
                    }
                }
            }
        }

        // 合并中央级别设置覆盖（modules.settings）
        // 有 provider 的模块按 provider 声明的 configKey() 合并，否则按配置文件 key 兜底。
        // try-catch：config:cache 首次执行时数据库可能还不存在
        if (empty($packageNames)) {
            return;
        }

        try {
            $moduleRecords = ModuleModel::whereIn('package_name', $packageNames)
                ->whereNotNull('settings')
                ->get()
                ->keyBy('package_name');

            foreach ($moduleRecords as $packageName => $moduleRecord) {
                $overrides = $moduleRecord->settings ?? [];
                if (empty($overrides)) {
                    continue;
                }

                $providerClass = $moduleRecord->provider_class;
                $configKey = null;

                if ($providerClass && is_subclass_of($providerClass, ModuleBaseProvider::class)) {
                    $configKey = (new $providerClass($this->app))->configKey();
                }

                if ($configKey !== null) {
                    $current = $this->app['config']->get($configKey, []);

                    $this->app['config']->set($configKey, array_replace_recursive($current, $overrides));

                    continue;
                }

                $namespace = str_replace('/', '.', $packageName);

                foreach ($overrides as $fileKey => $value) {
                    if (! is_array($value)) {
                        continue;
                    }

                    $configKey = $namespace.'.'.$fileKey;
                    $current = $this->app['config']->get($configKey, []);

                    $this->app['config']->set($configKey, array_replace_recursive($current, $value));
                }
            }
        } catch (\Throwable) {
            // 数据库不可用时跳过中央覆盖，模块默认配置仍然生效
        }
    }

    /**
     * 启动服务。
     *
     * boot() 在中央上下文中执行（早于任何 tenancy 中间件），
     * 因此在此处安全加载 central 区域模块。
     */
    public function boot(): void
    {
        // 框架级守卫：任何模块的租户路由均可使用 module.enabled:{package_name}
        Route::aliasMiddleware('module.enabled', EnsureModuleEnabled::class);

        /** @var ModuleManager $manager */
        $manager = $this->app->make(ModuleManager::class);

        // 数据库可能尚未迁移（如首次部署或测试环境），优雅降级
        try {
            $manager->loadCentralModules();
        } catch (\Throwable) {
            // modules 表不存在时跳过，模块加载将在后续请求中正常执行
        }

        $this->registerTenancyEventListeners();
    }

    /**
     * 注册 tenancy 事件监听器，以在租户初始化/结束时加载/卸载模块。
     */
    protected function registerTenancyEventListeners(): void
    {
        Event::listen(TenancyInitialized::class, function (TenancyInitialized $event) {
            /** @var Tenant $tenant */
            $tenant = $event->tenancy->tenant;

            /** @var ModuleManager $manager */
            $manager = app(ModuleManager::class);
            $manager->loadTenantModules($tenant);
        });

        Event::listen(TenancyEnded::class, function () {
            // 清理租户模块相关的缓存和状态
            /** @var ModuleManager $manager */
            $manager = app(ModuleManager::class);
            $manager->flushCache();
        });
    }
}
