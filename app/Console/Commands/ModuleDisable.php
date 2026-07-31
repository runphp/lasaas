<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Module\ModuleManager;
use Illuminate\Console\Command;

/**
 * 禁用模块。
 *
 * 调用模块的 onDisable() 钩子。
 */
class ModuleDisable extends Command
{
    protected $signature = 'module:disable {package : Composer 包名，如 lasaas/demo-module}';

    protected $description = '禁用模块';

    public function handle(ModuleManager $manager): int
    {
        $packageName = $this->argument('package');

        $module = Module::where('package_name', $packageName)->first();

        if (! $module) {
            $this->error("Module '{$packageName}' not found.");

            return self::FAILURE;
        }

        if ($module->status === ModuleStatus::INACTIVE) {
            $this->info("Module '{$packageName}' is already inactive.");

            return self::SUCCESS;
        }

        $manager->disable($module);

        $this->info("Disabled module '{$packageName}'.");

        return self::SUCCESS;
    }
}
