<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\Tenant;
use App\Module\ModuleBootLoader;
use Illuminate\Console\Command;

/**
 * 禁用指定租户的模块。
 *
 * 仅把 tenant_modules.enabled 置为 false，模块的租户功能停止加载；
 * 数据与迁移保留，后续可重新启用。
 */
class ModuleTenantDisable extends Command
{
    protected $signature = 'module:tenant-disable {tenant : 租户 ID} {package : Composer 包名，如 lasaas/module-blog}';

    protected $description = '禁用指定租户的模块';

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
            $this->error("Module '{$packageName}' is not installed for tenant '{$tenantId}'.");

            return self::FAILURE;
        }

        if (! $tenantModule->enabled) {
            $this->info("Module '{$packageName}' is already disabled for tenant '{$tenantId}'.");

            return self::SUCCESS;
        }

        $manager->disableForTenant($module, $tenant);

        $this->info("Disabled module '{$packageName}' for tenant '{$tenantId}'.");

        return self::SUCCESS;
    }
}
