<?php

declare(strict_types=1);

namespace App\Providers;

use App\Module\CentralRouteManager;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * 中央路由服务提供者 —— 中央域名路由的唯一入口。
 *
 * 根目录 routes/web.php 与所有 central 模块的 routes/web.php 均不直接 require，
 * 统一由 map() 调用的 CentralRouteManager::dispatchAll() 在中央域名分组内注册，
 * 防止路由脱离域名限制、跨域名越权访问。
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * 执行中央路由统一分发。
     */
    public function map(): void
    {
        app(CentralRouteManager::class)->dispatchAll();
    }
}
