<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\Tenant;
use App\Module\ModuleMigrationService;
use Illuminate\Console\Command;

/**
 * 撤销模块在指定租户最近一次（或 N 次）租户迁移 batch。
 *
 * 用于回退租户侧模块升级引入的迁移，不影响更早的批次。
 */
class ModuleTenantMigrationRollback extends Command
{
    protected $signature = 'module:tenant-migration:rollback
                            {package : Composer 包名，如 lasaas/module-blog}
                            {tenant : 租户 ID}
                            {--step=1 : 撤销最近 N 个 batch}';

    protected $description = '撤销模块在指定租户最近 N 个租户迁移 batch';

    public function handle(ModuleMigrationService $migrations): int
    {
        $packageName = $this->argument('package');
        $tenantId = $this->argument('tenant');
        $step = (int) $this->option('step');

        $module = Module::where('package_name', $packageName)->first();

        if (! $module) {
            $this->error("Module '{$packageName}' not found.");

            return self::FAILURE;
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $this->error("Tenant '{$tenantId}' not found.");

            return self::FAILURE;
        }

        $migrations->rollbackLastBatchForTenant($module, $tenant, $step);

        $this->info("Rolled back {$step} tenant migration batch(es) for module '{$packageName}' on tenant '{$tenantId}'.");

        return self::SUCCESS;
    }
}
