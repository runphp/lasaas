<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Module\TenantRouteLoader;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\Console\Helper\Table;

/**
 * 列出指定租户应用启用的模块路由（该租户启用模块的 routes/tenant.php）。
 *
 * 与分发中间件一致：先解析模块路由文件清单（中央库读取）、再初始化租户、
 * 再注册模块路由，最后通过路由对象 id 差集筛选出本次注册的模块路由并渲染表格。
 * 初始化租户会把默认连接切到 tenant，注册完成后恢复。
 */
class TenancyRoutesListCommand extends Command
{
    protected $signature = 'tenancy:routes-list {tenant : 租户 id} {--path= : 仅显示路径以给定前缀开头的路由}';

    protected $description = '列出指定租户应用启用的模块路由';

    public function handle(): int
    {
        $tenant = Tenant::find($this->argument('tenant'));

        if ($tenant === null) {
            $this->components->error("租户不存在：{$this->argument('tenant')}");

            return self::FAILURE;
        }

        $moduleRoutes = $this->resolveTenantModuleRoutes($tenant);

        $this->renderRoutes($tenant, $moduleRoutes);

        return self::SUCCESS;
    }

    /**
     * 注册该租户的模块路由，并返回本次新增的路由对象。
     *
     * @return Route[]
     */
    protected function resolveTenantModuleRoutes(Tenant $tenant): array
    {
        $loader = app(TenantRouteLoader::class);

        // Module route files must be resolved from the central DB before tenancy init.
        $routeFiles = $loader->resolveRouteFilesFor($tenant);

        $router = app('router');

        $beforeIds = array_map(
            fn (Route $route): int => spl_object_id($route),
            $router->getRoutes()->getRoutes(),
        );

        $defaultConnection = DB::getDefaultConnection();

        try {
            app(Tenancy::class)->initialize($tenant);

            if ($routeFiles !== []) {
                $loader->registerRouteFiles($routeFiles);
            }
        } finally {
            DB::setDefaultConnection($defaultConnection);
        }

        return array_values(array_filter(
            $router->getRoutes()->getRoutes(),
            fn (Route $route): bool => ! in_array(spl_object_id($route), $beforeIds, true),
        ));
    }

    /**
     * @param  Route[]  $routes
     */
    protected function renderRoutes(Tenant $tenant, array $routes): void
    {
        $pathFilter = $this->option('path');

        $this->components->info("租户 [{$tenant->getTenantKey()}] 模块路由：");

        if ($routes === []) {
            $this->components->warn('该租户未启用任何模块路由。');

            return;
        }

        $rows = [];

        foreach ($routes as $route) {
            if ($pathFilter !== null && ! str_starts_with($route->uri(), $pathFilter)) {
                continue;
            }

            $rows[] = [
                implode('|', $route->methods()),
                $route->uri(),
                $route->getName() ?? '',
                ltrim($route->getActionName(), '\\'),
            ];
        }

        if ($rows === []) {
            $this->components->warn("没有路径以 [{$pathFilter}] 开头的模块路由。");

            return;
        }

        (new Table($this->output))
            ->setHeaders(['Method', 'URI', 'Name', 'Action'])
            ->setRows($rows)
            ->render();
    }
}
