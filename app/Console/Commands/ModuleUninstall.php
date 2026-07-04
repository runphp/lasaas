<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Module;
use App\Module\ModuleManager;
use Illuminate\Console\Command;

/**
 * 卸载模块。
 *
 * 调用模块的 uninstall() 钩子（回滚迁移、清理数据），然后删除数据库记录。
 */
class ModuleUninstall extends Command
{
    protected $signature = 'module:uninstall
                            {package : Composer 包名，如 lasaas/demo-module}
                            {--force : 跳过确认}';

    protected $description = '卸载模块并清理数据';

    public function handle(ModuleManager $manager): int
    {
        $packageName = $this->argument('package');

        $module = Module::where('package_name', $packageName)->first();

        if (! $module) {
            $this->error("Module '{$packageName}' not found.");

            return self::FAILURE;
        }

        if (! $module->isInstalled()) {
            // 从未安装过，直接删记录即可
            $module->delete();
            $this->info("Removed uninstalled module record '{$packageName}'.");

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->warn("⚠  This will run the module's uninstall hook and delete its database record.");
            $this->warn("   Module: {$module->name} ({$packageName})");
            $this->warn('   Status: '.($module->status === 'active' ? 'active (will be disabled first)' : 'inactive'));

            if (! $this->confirm('Are you sure you want to uninstall this module?')) {
                $this->info('Cancelled.');

                return self::SUCCESS;
            }
        }

        if ($module->status === 'active') {
            $manager->disable($module);
        }

        $manager->uninstall($module);

        $this->info("Uninstalled module '{$packageName}'.");

        return self::SUCCESS;
    }
}
