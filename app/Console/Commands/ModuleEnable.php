<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Module\ModuleBootLoader;
use Illuminate\Console\Command;

/**
 * 启用模块。
 *
 * 首次启用会调用模块的 install() 钩子（运行迁移、创建默认数据等）。
 */
class ModuleEnable extends Command
{
    protected $signature = 'module:enable {package : Composer 包名，如 lasaas/demo-module}';

    protected $description = '启用模块';

    public function handle(ModuleBootLoader $manager): int
    {
        $packageName = $this->argument('package');

        $module = Module::where('package_name', $packageName)->first();

        if (! $module) {
            $this->error("Module '{$packageName}' not found. Run module:sync first.");

            return self::FAILURE;
        }

        if ($module->status === ModuleStatus::ACTIVE) {
            $this->info("Module '{$packageName}' is already active.");

            return self::SUCCESS;
        }

        $manager->enable($module);

        $label = $module->isInstalled() ? 'Enabled' : 'Installed & enabled';

        $this->info("{$label} module '{$packageName}'.");

        return self::SUCCESS;
    }
}
