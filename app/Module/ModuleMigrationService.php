<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Module;
use App\Models\Tenant;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\Migrator;

/**
 * 模块迁移服务 —— 模块迁移的唯一执行者，与系统 migrate 完全隔离。
 *
 * 职责：
 *  - 中央迁移：跑模块 packages 内 database/migrations 目录；
 *  - 租户迁移：在指定租户数据库内跑 database/migrations/tenant 目录；
 *  - 全部记录写入独立表 module_migrations（module_id + 模块内独立 batch），
 *    不再与系统 migrations 表混淆。
 *
 * 语义：
 *  - migrate()           —— 安装/升级，幂等（只跑尚未记录的 pending 迁移）；
 *  - rollbackLastBatch() —— 撤销最近 N 个 batch（升级回退）；
 *  - rollback()          —— reset 全部迁移并删除对应表（卸载）；
 *  - purge()             —— 清空该模块全部迁移记录（卸载兜底，即使文件已删除）。
 *
 * 迁移目录不存在时静默跳过（模块可能只有中央或只有租户迁移，或尚未下载源码）。
 */
class ModuleMigrationService
{
    public function __construct(
        protected Application $app,
    ) {}

    // ---------------------------------------------------------------
    // 中央迁移
    // ---------------------------------------------------------------

    /**
     * 运行模块中央迁移（安装/升级，幂等）。
     */
    public function migrate(Module $module): void
    {
        $this->run($module, 'database/migrations', 'up');
    }

    /**
     * 撤销模块最近 N 个中央迁移 batch（默认 1 个）。
     */
    public function rollbackLastBatch(Module $module, int $step = 1): void
    {
        for ($i = 0; $i < max(1, $step); $i++) {
            $this->run($module, 'database/migrations', 'rollback');
        }
    }

    /**
     * 回滚模块全部中央迁移并删除对应表（卸载）。
     */
    public function rollback(Module $module): void
    {
        $this->run($module, 'database/migrations', 'reset');
    }

    /**
     * 清空模块中央迁移记录（卸载兜底）。
     */
    public function purge(Module $module): void
    {
        (new ModuleMigrationRepository($this->app['db'], (int) $module->id))->purge();
    }

    // ---------------------------------------------------------------
    // 租户迁移
    // ---------------------------------------------------------------

    /**
     * 在指定租户库运行模块租户迁移（首次安装/升级，幂等）。
     */
    public function migrateForTenant(Module $module, Tenant $tenant): void
    {
        $this->withTenancy($tenant, fn () => $this->run($module, 'database/migrations/tenant', 'up'));
    }

    /**
     * 在指定租户库撤销模块最近 N 个租户迁移 batch（默认 1 个）。
     */
    public function rollbackLastBatchForTenant(Module $module, Tenant $tenant, int $step = 1): void
    {
        $this->withTenancy($tenant, function () use ($module, $step): void {
            for ($i = 0; $i < max(1, $step); $i++) {
                $this->run($module, 'database/migrations/tenant', 'rollback');
            }
        });
    }

    /**
     * 在指定租户库回滚模块全部租户迁移并删除对应表（卸载）。
     */
    public function rollbackForTenant(Module $module, Tenant $tenant): void
    {
        $this->withTenancy($tenant, fn () => $this->run($module, 'database/migrations/tenant', 'reset'));
    }

    /**
     * 清空模块在指定租户库的迁移记录（卸载兜底）。
     */
    public function purgeForTenant(Module $module, Tenant $tenant): void
    {
        $this->withTenancy($tenant, fn () => $this->purge($module));
    }

    /**
     * 为所有启用该模块的租户批量运行租户迁移。
     *
     * @return int 处理的租户数
     */
    public function migrateForEnabledTenants(Module $module): int
    {
        $count = 0;

        foreach ($module->tenantModules()->where('enabled', true)->with('tenant')->get() as $tenantModule) {
            $this->migrateForTenant($module, $tenantModule->tenant);

            $count++;
        }

        return $count;
    }

    // ---------------------------------------------------------------
    // 内部实现
    // ---------------------------------------------------------------

    /**
     * 用隔离的 Migrator 实例执行指定迁移操作。
     *
     * 每次新建 Migrator + ModuleMigrationRepository，只作用于当前模块，
     * 不影响共享的 app['migrator']（系统 migrate 仍只认 migrations 表）。
     */
    protected function run(Module $module, string $relativePath, string $method): void
    {
        $path = $this->resolveMigrationPath($module, $relativePath);

        if (! is_dir($path)) {
            return;
        }

        $repository = new ModuleMigrationRepository($this->app['db'], (int) $module->id);

        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }

        $migrator = new Migrator(
            $repository,
            $this->app['db'],
            $this->app['files'],
            $this->app['events'],
        );

        match ($method) {
            'up' => $migrator->run($path),
            'rollback' => $migrator->rollback($path),
            'reset' => $migrator->reset($path),
        };
    }

    /**
     * 解析模块迁移目录绝对路径（基于 modules.path，DB 记录缺失时按包名兜底）。
     */
    protected function resolveMigrationPath(Module $module, string $relativePath): string
    {
        $base = $module->path;

        if (! $base) {
            $base = base_path('packages/custom/'.$module->package_name);

            if (! is_dir($base)) {
                $base = base_path('packages/contrib/'.$module->package_name);
            }
        }

        return rtrim($base, '/').'/'.ltrim($relativePath, '/');
    }

    /**
     * 在租户上下文中执行回调，结束后恢复中央上下文。
     */
    protected function withTenancy(Tenant $tenant, Closure $callback): void
    {
        if (tenancy()->initialized) {
            $callback();

            return;
        }

        tenancy()->initialize($tenant);

        try {
            $callback();
        } finally {
            tenancy()->end();
        }
    }
}
