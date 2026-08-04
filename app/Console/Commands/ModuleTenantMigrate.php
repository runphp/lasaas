<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\Tenant;
use App\Module\ModuleMigrationService;
use Illuminate\Console\Command;

/**
 * 运行模块租户迁移（安装/升级）。
 *
 * 指定租户：仅对该租户库运行 pending 迁移；
 * 省略租户：为所有启用该模块的租户批量补跑。
 */
class ModuleTenantMigrate extends Command
{
    protected $signature = 'module:tenant-migrate
                            {package : Composer 包名，如 lasaas/module-blog}
                            {tenant? : 租户 ID，省略则为所有启用该模块的租户运行}';

    protected $description = '运行模块租户迁移（幂等，只跑未执行的迁移）';

    public function handle(ModuleMigrationService $migrations): int
    {
        $packageName = $this->argument('package');
        $tenantId = $this->argument('tenant');

        $module = Module::where('package_name', $packageName)->first();

        if (! $module) {
            $this->error("Module '{$packageName}' not found. Run module:sync first.");

            return self::FAILURE;
        }

        if ($tenantId !== null) {
            $tenant = Tenant::find($tenantId);

            if (! $tenant) {
                $this->error("Tenant '{$tenantId}' not found.");

                return self::FAILURE;
            }

            $migrations->migrateForTenant($module, $tenant);

            $this->info("Ran pending tenant migrations for module '{$packageName}' on tenant '{$tenantId}'.");

            return self::SUCCESS;
        }

        $count = $migrations->migrateForEnabledTenants($module);

        $this->info("Ran pending tenant migrations for module '{$packageName}' on {$count} enabled tenant(s).");

        return self::SUCCESS;
    }
}
