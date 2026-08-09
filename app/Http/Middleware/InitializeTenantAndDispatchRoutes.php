<?php

namespace App\Http\Middleware;

use App\Module\TenantRouteCache;
use App\Module\TenantRouteLoader;
use App\Tenancy\TenantAvailability;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * 域名 → 租户路由分发中间件（注册于全局中间件栈首位）。
 *
 * 每个请求只有一种路由集合：
 * - 中央域名：加载中央路由缓存（central.php），不初始化任何租户。
 * - 租户域名：解析租户 → 中央库读取模块路由清单（必须在切换连接前）→
 *   可用性校验 → 初始化租户 → 加载租户路由缓存或动态注册模块路由。
 *
 * 位于 StartSession（web 组）之前，保证会话落租户连接，
 * 也因此获得每个租户独立的 Session 实例与路由集合。
 */
class InitializeTenantAndDispatchRoutes
{
    public function __construct(
        protected Tenancy $tenancy,
        protected DomainTenantResolver $resolver,
        protected TenantRouteLoader $loader,
        protected TenantAvailability $availability,
    ) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if (in_array($host, config('tenancy.central_domains', []), true)) {
            $this->loadCachedRoutes('central');

            return $next($request);
        }

        try {
            $tenant = $this->resolver->resolve($host);
        } catch (TenantCouldNotBeIdentifiedOnDomainException) {
            throw new HttpException(404, 'Unknown domain');
        }

        // 模块路由文件清单必须在租户连接切换前从中央库解析。
        $routeFiles = $this->loader->resolveRouteFilesFor($tenant);

        if (($response = $this->availability->check($tenant)) !== null) {
            return $response;
        }

        // stancl 初始化会把默认连接切到 tenant；请求结束后需恢复，
        // 否则会泄漏到测试 teardown 等后续流程。
        $defaultConnection = DB::getDefaultConnection();

        $this->tenancy->initialize($tenant);

        try {
            if (! $this->loadCachedRoutes(TenantRouteCache::tenantKey($tenant))) {
                $this->loader->registerRouteFiles($routeFiles);
            }

            return $next($request);
        } finally {
            DB::setDefaultConnection($defaultConnection);
        }
    }

    /**
     * 加载指定路由缓存文件；不存在或加载失败时回退动态注册。
     */
    protected function loadCachedRoutes(string $key): bool
    {
        $file = TenantRouteCache::fileFor($key);

        if (! is_file($file)) {
            return false;
        }

        try {
            require $file;

            return true;
        } catch (Throwable $e) {
            Log::warning('Failed to load route cache, falling back to dynamic registration.', [
                'file' => $file,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
