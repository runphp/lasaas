<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Module;
use Illuminate\Support\Facades\Route;

/**
 * 中央路由管理器 —— 统一处理中央域名下所有网页路由。
 *
 * 路由注册约定：中央域名下的所有网页路由（根目录 routes/web.php + 各 central
 * 模块的 routes/web.php）一律通过 dispatchAll() 在 Route::domain() 分组内注册，
 * 不在任何其他地方直接 require，杜绝路由脱离域名限制、跨域名越权访问。
 *
 * 与租户路由完全隔离：本类不触碰任何 tenant 区域路由
 * （租户路由由 ModuleBootLoader::registerTenantModuleRoutes() 在 boot 阶段注册）。
 *
 * 流程：
 * 1. 收集阶段：把路由文件封装为闭包存入 CentralRouteQueue（不立即 require）
 * 2. 分发阶段：遍历中央域名，在域名分组内批量执行全部闭包
 */
class CentralRouteManager
{
    public function __construct(
        protected ModuleDiscoveryManager $discovery,
        protected CentralRouteQueue $queue,
    ) {}

    /**
     * 将项目根目录 routes/web.php 封装闭包存入路由队列。
     */
    public function collectBaseWebRoute(): void
    {
        $this->queue->push(fn () => require base_path('routes/web.php'));
    }

    /**
     * 遍历所有 active 且支持 central 区域的模块，将其 routes/web.php 封装闭包入队。
     */
    public function collectAllCentralModuleRoutes(): void
    {
        $modules = $this->discovery->discover()
            ->filter(fn (Module $module): bool => $this->discovery->supportsArea($module, 'central'));

        foreach ($modules as $module) {
            $path = $module->path.'/routes/web.php';

            if (! file_exists($path)) {
                continue;
            }

            $this->queue->push(fn () => require $path);
        }
    }

    /**
     * 统一分发入口：全局仅遍历一次中央域名，批量注册根 web + 全部模块中央路由。
     *
     * 由 App\Providers\RouteServiceProvider::map() 调用，是中央路由的唯一入口。
     */
    public function dispatchAll(): void
    {
        $this->collectBaseWebRoute();
        $this->collectAllCentralModuleRoutes();

        if ($this->queue->isEmpty()) {
            return;
        }

        $loaders = $this->queue->all();
        $this->queue->flush();

        foreach (config('tenancy.central_domains') as $domain) {
            Route::domain($domain)->middleware('web')->group(function () use ($loaders): void {
                foreach ($loaders as $loader) {
                    $loader();
                }
            });
        }
    }
}
