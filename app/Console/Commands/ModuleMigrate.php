<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Module;
use App\Module\ModuleMigrationService;
use Illuminate\Console\Command;

/**
 * 运行模块中央迁移（安装/升级）。
 *
 * 只运行模块 database/migrations 目录中尚未执行的迁移（幂等）。
 * 已安装模块升级后新增的迁移文件，通过本命令即可补跑，无需重新启用模块。
 */
class ModuleMigrate extends Command
{
    protected $signature = 'module:migrate {package : Composer 包名，如 lasaas/demo-module}';

    protected $description = '运行模块中央迁移（幂等，只跑未执行的迁移）';

    public function handle(ModuleMigrationService $migrations): int
    {
        $packageName = $this->argument('package');

        $module = Module::where('package_name', $packageName)->first();

        if (! $module) {
            $this->error("Module '{$packageName}' not found. Run module:sync first.");

            return self::FAILURE;
        }

        $migrations->migrate($module);

        $this->info("Ran pending central migrations for module '{$packageName}'.");

        return self::SUCCESS;
    }
}
