<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\Tenant;
use App\Module\ModuleBootLoader;
use Illuminate\Console\Command;

/**
 * 从指定租户卸载模块。
 *
 * 调用模块的 tenantUninstall() 钩子（默认回滚租户库迁移），然后删除 tenant_modules 记录。
 */
class ModuleTenantUninstall extends Command
{
    protected $signature = 'module:tenant-uninstall {tenant : 租户 ID} {package : Composer 包名，如 lasaas/module-blog}';

    protected $description = '从指定租户卸载模块';

    public function handle(ModuleBootLoader $manager): int
    {
        $packageName = $this->argument('package');
        $tenantId = $this->argument('tenant');

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

        $tenantModule = $tenant->tenantModules()->where('module_id', $module->id)->first();

        if (! $tenantModule) {
            $this->info("Module '{$packageName}' is not installed for tenant '{$tenantId}'.");

            return self::SUCCESS;
        }

        $manager->uninstallForTenant($module, $tenant);

        $this->info("Uninstalled module '{$packageName}' from tenant '{$tenantId}'.");

        return self::SUCCESS;
    }
}
