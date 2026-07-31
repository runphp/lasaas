<?php

declare(strict_types=1);

namespace App\Module\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 租户模块启用守卫。
 *
 * 模块的租户路由在中央应用启用后全局注册，但只有
 * 已安装并启用该模块的租户才能访问（未启用返回 404）。
 */
class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $packageName): Response
    {
        $tenant = tenancy()->tenant;

        if (! $tenant instanceof Tenant || ! in_array($packageName, $tenant->getEnabledModules(), true)) {
            abort(404);
        }

        return $next($request);
    }
}
