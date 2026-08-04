<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Module;
use App\Module\ModuleMigrationService;
use Illuminate\Console\Command;

/**
 * 撤销模块最近一次（或 N 次）中央迁移 batch。
 *
 * 用于回退模块升级引入的迁移，不影响更早的批次。
 */
class ModuleMigrationRollback extends Command
{
    protected $signature = 'module:migration:rollback
                            {package : Composer 包名，如 lasaas/demo-module}
                            {--step=1 : 撤销最近 N 个 batch}';

    protected $description = '撤销模块最近 N 个中央迁移 batch';

    public function handle(ModuleMigrationService $migrations): int
    {
        $packageName = $this->argument('package');
        $step = (int) $this->option('step');

        $module = Module::where('package_name', $packageName)->first();

        if (! $module) {
            $this->error("Module '{$packageName}' not found.");

            return self::FAILURE;
        }

        if (! $module->isInstalled()) {
            $this->warn("Module '{$packageName}' has never been installed; nothing to roll back.");

            return self::SUCCESS;
        }

        $migrations->rollbackLastBatch($module, $step);

        $this->info("Rolled back {$step} migration batch(es) for module '{$packageName}'.");

        return self::SUCCESS;
    }
}
