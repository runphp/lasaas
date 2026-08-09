<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Tenant;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/**
 * 租户路由加载器 —— 加载当前租户启用模块的 routes/tenant.php。
 *
 * 由两个入口使用：
 * - InitializeTenantAndDispatchRoutes（全局中间件）：先 resolveRouteFilesFor() 计算文件清单
 *   （中央库读取，必须在租户连接切换前），初始化租户后再 registerRouteFiles()。
 * - TenancyCacheRoutesCommand：缓存构建时同样「先算清单、后初始化」。
 *
 * 只加载该租户启用模块的路由，跨租户冲突天然消除；同租户内多模块冲突在注册时显式抛出。
 * 模块路由组不带域名/tenancy 包裹：域名正确性由分发中间件保证，
 * 基础中间件仅保留 'web'（会话由 web 组启动，位于分发中间件之后，落正确连接）。
 */
class TenantRouteLoader
{
    public function __construct(
        protected ModuleDiscoveryManager $discovery,
    ) {}

    /**
     * 解析当前租户启用模块的 routes/tenant.php 文件清单。
     *
     * 查询模块表与 tenant_modules 表（均为中央库），必须在租户连接切换前调用。
     *
     * @return string[] 存在的路由文件绝对路径
     */
    public function resolveRouteFilesFor(Tenant $tenant): array
    {
        $enabledPackages = $tenant->getEnabledModules();

        if (empty($enabledPackages)) {
            return [];
        }

        $files = [];

        foreach ($this->discovery->discover() as $module) {
            if (! $this->discovery->supportsArea($module, 'tenant')) {
                continue;
            }

            if (! in_array($module->package_name, $enabledPackages, true)) {
                continue;
            }

            $path = $module->path.'/routes/tenant.php';

            if (file_exists($path)) {
                $files[] = $path;
            }
        }

        return array_values(array_unique($files));
    }

    /**
     * 注册路由文件并做同租户路由冲突检测。
     *
     * @param  string[]  $files
     */
    public function registerRouteFiles(array $files): void
    {
        $router = app('router');
        $seenObjectIds = array_fill_keys($this->routeIds($router), true);
        $signatures = [];

        foreach ($files as $file) {
            Route::middleware('web')->group($file);

            $this->assertNoConflicts($router, $seenObjectIds, $signatures);
        }
    }

    /**
     * 便捷入口：解析 + 注册（须在中央上下文调用，或确保已提前 resolve）。
     */
    public function load(Tenant $tenant): void
    {
        $this->registerRouteFiles($this->resolveRouteFilesFor($tenant));
    }

    /**
     * @return array<int, int> spl_object_id 集合，用于识别本次新增的路由
     */
    protected function routeIds(Router $router): array
    {
        return array_map(
            fn ($route): int => spl_object_id($route),
            $router->getRoutes()->getRoutes(),
        );
    }

    /**
     * 仅对本次注册新增的路由做冲突检测，避免把
     * 「中央路由（带域名约束）与租户路由同路径」误判为冲突。
     *
     * 逐个文件注册后即时检测：RouteCollection 会把同 key（method+domain+uri）
     * 的后注册路由替换掉先注册的路由，若只对比最终集合，被覆盖的路由对象
     * 已经消失，跨文件冲突会漏检。
     *
     * @param  array<int, true>  $seenObjectIds
     * @param  array<string, string>  $signatures
     */
    protected function assertNoConflicts(Router $router, array &$seenObjectIds, array &$signatures): void
    {
        foreach ($router->getRoutes()->getRoutes() as $route) {
            if (isset($seenObjectIds[spl_object_id($route)])) {
                continue;
            }

            $signature = $route->methods()[0].' '.$route->uri();

            if (isset($signatures[$signature])) {
                throw new TenantRouteConflictException(
                    "同租户路由冲突：{$signature}（{$signatures[$signature]} 与 {$route->getActionName()}）"
                );
            }

            $signatures[$signature] = $route->getActionName();
            $seenObjectIds[spl_object_id($route)] = true;
        }
    }
}
