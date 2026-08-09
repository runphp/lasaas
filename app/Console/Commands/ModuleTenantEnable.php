<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Models\Tenant;
use App\Module\ModuleBootLoader;
use Illuminate\Console\Command;

/**
 * 为指定租户安装/启用模块。
 *
 * 中央应用启用模块后，如需给某个租户提供模块的租户功能，
 * 通过本命令按租户安装：创建 tenant_modules 记录，并在租户库运行模块迁移。
 */
class ModuleTenantEnable extends Command
{
    protected $signature = 'module:tenant-enable {tenant : 租户 ID} {package : Composer 包名，如 lasaas/module-blog}';

    protected $description = '为指定租户安装并启用模块';

    public function handle(ModuleBootLoader $manager): int
    {
        $packageName = $this->argument('package');
        $tenantId = $this->argument('tenant');

        $module = Module::where('package_name', $packageName)->first();

        if (! $module) {
            $this->error("Module '{$packageName}' not found. Run module:sync first.");

            return self::FAILURE;
        }

        if ($module->status !== ModuleStatus::ACTIVE) {
            $this->error("Module '{$packageName}' is not active in the central app. Run module:enable first.");

            return self::FAILURE;
        }

        if (! in_array('tenant', $module->areas ?? [], true)) {
            $this->error("Module '{$packageName}' does not support the tenant area.");

            return self::FAILURE;
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $this->error("Tenant '{$tenantId}' not found.");

            return self::FAILURE;
        }

        $manager->enableForTenant($module, $tenant);

        $this->info("Installed & enabled module '{$packageName}' for tenant '{$tenant->id}'.");

        return self::SUCCESS;
    }
}
