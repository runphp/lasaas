<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Module\TenantRouteCache;
use App\Module\TenantRouteLoader;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\RouteCollection;
use Stancl\Tenancy\Tenancy;

/**
 * 预编译中央应用与全部租户的应用路由缓存。
 *
 * 结构（bootstrap/cache/tenant-routes/）：
 * - central.php                  中央应用路由集合
 * - tenant_{sha1(tenantKey)}.php 每个租户的应用路由集合
 *
 * 每个租户上下文都在独立 fresh app 中构建：
 * 先由引导完成中央/框架/面板路由注册，再在 booted 回调中
 * 「先解析模块路由文件清单（中央库读取）、后初始化租户、再注册模块路由」，
 * 最后对整个路由集合序列化写入缓存文件。
 */
class TenancyCacheRoutesCommand extends Command
{
    protected $signature = 'tenancy:routes-cache';

    protected $description = '预编译中央应用与全部租户的应用路由缓存';

    public function handle(): int
    {
        // 标准 route:cache 缓存会绕过按域名分发，必须清除
        $this->callSilently('route:clear');

        TenantRouteCache::prepareDirectory();

        // 租户清单必须在构建 fresh app 之前从中央库收集
        $tenants = Tenant::query()->get();

        $this->components->info('Caching central routes...');

        $centralCount = $this->buildCentralRoutes();

        $this->components->info("Caching routes for {$tenants->count()} tenants...");

        $tenantTotal = 0;

        foreach ($tenants as $tenant) {
            $count = $this->buildTenantRoutes($tenant);
            $tenantTotal += $count;
            $this->components->twoColumnDetail((string) $tenant->getTenantKey(), "{$count} routes");
        }

        $this->components->info(sprintf(
            'Route caches written: central (%d routes) + %d tenants (%d routes total).',
            $centralCount,
            $tenants->count(),
            $tenantTotal,
        ));

        return self::SUCCESS;
    }

    protected function buildCentralRoutes(): int
    {
        $app = require $this->laravel->bootstrapPath('app.php');

        $app->make(ConsoleKernelContract::class)->bootstrap();

        $count = $this->writeCache($app['router']->getRoutes(), TenantRouteCache::fileFor('central'));

        // fresh app 构造时已将自身设为全局容器实例，构建完成后恢复
        Container::setInstance($this->laravel);

        return $count;
    }

    protected function buildTenantRoutes(Tenant $tenant): int
    {
        $app = require $this->laravel->bootstrapPath('app.php');

        $app->booted(function () use ($app, $tenant): void {
            $loader = $app->make(TenantRouteLoader::class);

            // 模块路由清单必须先从中央库解析（此时默认连接仍是中央库）
            $routeFiles = $loader->resolveRouteFilesFor($tenant);

            $app->make(Tenancy::class)->initialize($tenant);

            if ($routeFiles !== []) {
                $loader->registerRouteFiles($routeFiles);
            }
        });

        $app->make(ConsoleKernelContract::class)->bootstrap();

        $count = $this->writeCache($app['router']->getRoutes(), TenantRouteCache::fileFor(TenantRouteCache::tenantKey($tenant)));

        Container::setInstance($this->laravel);

        return $count;
    }

    protected function writeCache(RouteCollection $routes, string $path): int
    {
        $routes->refreshNameLookups();
        $routes->refreshActionLookups();

        foreach ($routes->getRoutes() as $route) {
            $route->prepareForSerialization();
        }

        (new Filesystem)->put($path, $this->buildRouteCacheFile($routes));

        return count($routes);
    }

    protected function buildRouteCacheFile(RouteCollection $routes): string
    {
        $stub = (new Filesystem)->get(base_path('vendor/laravel/framework/src/Illuminate/Foundation/Console/stubs/routes.stub'));

        return str_replace('{{routes}}', var_export($routes->compile(), true), $stub);
    }
}
