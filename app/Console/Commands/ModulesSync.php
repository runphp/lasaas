<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Module;
use App\Module\ModuleDiscoveryManager;
use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * 将 packages/contrib/ 和 packages/custom/ 中的 lasaas-module 包同步到 modules 数据库表。
 *
 * 参考 Drupal 模块系统设计：模块的存亡以文件系统为准。
 * - 磁盘上存在的模块 → upsert 到数据库
 * - 磁盘上已删除的模块 → 从数据库物理删除（--soft 可改为标记 inactive）
 * - 自动分析模块间依赖关系
 * - 检测模块 ServiceProvider
 */
class ModulesSync extends Command
{
    protected $signature = 'module:sync
                            {--dry-run : 仅模拟，不写入数据库}
                            {--force : 强制重新同步所有模块元数据}
                            {--soft : 磁盘上已删除的模块仅标记为 inactive，不物理删除}';

    protected $description = '将已安装的 lasaas-module 包同步到数据库';

    /**
     * 已安装的 lasaas-module 类型包的缓存
     */
    protected Collection $installedModulePackages;

    public function handle(): int
    {
        $this->installedModulePackages = $this->scanInstalledModulePackages();

        if ($this->installedModulePackages->isEmpty()) {
            $this->info('No lasaas-module packages found in packages/contrib/ or packages/custom/.');

            return self::SUCCESS;
        }

        $this->info('Found '.$this->installedModulePackages->count().' installed module package(s).');
        $this->newLine();

        $added = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($this->installedModulePackages as $packageName => $packageInfo) {
            $module = Module::where('package_name', $packageName)->first();

            if ($module && ! $this->option('force')) {
                // 已有记录且非强制模式，只更新版本号
                if ($this->option('dry-run')) {
                    $this->line("  <fg=yellow>SKIP</> {$packageName} (already exists, use --force to re-sync)");
                } else {
                    $module->update(['version' => $packageInfo['version']]);
                }
                $skipped++;

                continue;
            }

            $data = $this->buildModuleData($packageName, $packageInfo);
            if ($module) {
                unset($data['status']);
            }
            if ($this->option('dry-run')) {
                $action = $module ? 'UPDATE' : 'CREATE';
                $this->line("  <fg=green>{$action}</> {$packageName}");
                $this->line('    description: '.$data['description']);
                $this->line('    providers: '.json_encode($data['providers']));
                $this->line('    areas: '.json_encode($data['areas']));
                $this->line('    dependencies: '.json_encode($data['dependencies']));
                $module ? $updated++ : $added++;

                continue;
            }

            Module::updateOrCreate(
                ['package_name' => $packageName],
                $data,
            );

            $action = $module ? 'Updated' : 'Created';
            $this->line("  <fg=green>{$action}</> {$packageName}");
            $module ? $updated++ : $added++;
        }

        // 数据库中仍为 active 但磁盘上已不存在的模块：默认删除，--soft 时标记 inactive
        $pruned = $this->markRemovedModules();

        $this->newLine();
        $this->info("Sync complete. Added: {$added}, Updated: {$updated}, Skipped: {$skipped}, Pruned: {$pruned}.");

        if ($this->option('dry-run')) {
            $this->warn('Dry-run mode — no changes were written to database.');

            return self::SUCCESS;
        }

        // 生成模块 autoload 文件，Composer 静态加载，参考 Drupal 设计
        $this->generateAutoloadFile();

        // 清除面板插件发现缓存，下次请求自动重建
        app(ModuleDiscoveryManager::class)->flushPanelPluginsCache();

        return self::SUCCESS;
    }

    /**
     * 生成模块 PSR-4 autoload 缓存文件。
     *
     * 将当前所有活跃模块的 PSR-4 映射写入 bootstrap/cache/lasaas_modules_autoload.php，
     * 由 ModuleServiceProvider::register() 在启动时安全加载。
     *
     * 文件存放在 bootstrap/cache/ 而非 vendor/ 目录，确保：
     * - 不受 composer install 影响（vendor/ 会被重建）
     * - 与 Laravel 的 packages.php、services.php 缓存文件保持一致
     *
     * 参考 Drupal：autoload 由构建步骤生成，不依赖运行时动态扫描。
     */
    protected function generateAutoloadFile(): void
    {
        $autoloadPath = base_path('bootstrap/cache/lasaas_modules_autoload.php');

        $dir = dirname($autoloadPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // 收集所有活跃模块的 PSR-4 映射
        $psr4Mappings = [];

        foreach ($this->installedModulePackages as $packageName => $packageInfo) {
            $composerJson = $packageInfo['composer_json'];
            $psr4 = $composerJson['autoload']['psr-4'] ?? [];

            foreach ($psr4 as $namespace => $srcDir) {
                $absolutePath = $packageInfo['path'].'/'.rtrim($srcDir, '/');

                if (is_dir($absolutePath)) {
                    $psr4Mappings[] = [
                        'namespace' => $namespace,
                        'path' => $absolutePath,
                    ];
                }
            }
        }

        // 生成 PHP 文件内容
        $lines = [
            '<?php',
            '',
            '// 由 module:sync 自动生成，请勿手动编辑',
            '// 参考 Drupal：模块 autoload 由构建步骤生成，不依赖运行时扫描',
            '// 生成时间: '.now()->toIso8601String(),
            '',
            '$modulePsr4Maps = '.var_export($psr4Mappings, true).';',
            '',
            '// 获取 Composer ClassLoader 并注册模块 PSR-4 命名空间',
            '$loader = require base_path(\'vendor/autoload.php\');',
            '',
            'foreach ($modulePsr4Maps as $map) {',
            '    $loader->addPsr4($map[\'namespace\'], [$map[\'path\']], prepend: true);',
            '}',
            '',
        ];

        file_put_contents($autoloadPath, implode("\n", $lines));

        $count = count($psr4Mappings);
        $this->info("Generated module autoload file ({$count} PSR-4 mapping(s)).");
        $this->comment('Run "ddev composer dump-autoload" to refresh Composer autoload cache.');
    }

    /**
     * 扫描所有已安装的 lasaas-module 类型包。
     *
     * @return Collection<string, array>
     */
    protected function scanInstalledModulePackages(): Collection
    {
        $result = collect();

        $scanDirs = [
            'contrib' => base_path('packages/contrib'),
            'custom' => base_path('packages/custom'),
        ];

        foreach ($scanDirs as $origin => $basePath) {
            if (! is_dir($basePath)) {
                continue;
            }

            // 遍历 packages/{contrib|custom}/{vendor}/{name}/ 目录
            $vendorDirs = File::directories($basePath);

            foreach ($vendorDirs as $vendorDir) {
                $packageDirs = File::directories($vendorDir);

                foreach ($packageDirs as $packageDir) {
                    $composerJsonPath = $packageDir.'/composer.json';

                    if (! file_exists($composerJsonPath)) {
                        continue;
                    }

                    $composerJson = json_decode(file_get_contents($composerJsonPath), true);

                    if (! is_array($composerJson)) {
                        continue;
                    }

                    $type = $composerJson['type'] ?? '';

                    if ($type !== 'lasaas-module') {
                        continue;
                    }

                    $packageName = $composerJson['name'] ?? basename(dirname($packageDir)).'/'.basename($packageDir);

                    // 同名包优先 contrib，custom 覆盖 contrib（后扫的覆盖先扫的）
                    if ($result->has($packageName)) {
                        $this->warn("  Package '{$packageName}' exists in both contrib and custom; custom takes precedence.");
                    }

                    $result[$packageName] = [
                        'path' => $packageDir,
                        'origin' => $origin,
                        'composer_json' => $composerJson,
                        'version' => InstalledVersions::getPrettyVersion($packageName),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * 根据扫描结果构建模块数据数组。
     *
     * @return array<string, mixed>
     */
    protected function buildModuleData(string $packageName, array $packageInfo): array
    {
        $composerJson = $packageInfo['composer_json'];
        $path = $packageInfo['path'];

        // 提取描述
        $description = $composerJson['description'] ?? null;

        // 检测 Provider 类
        $providers = $composerJson['extra']['lasaas-module']['providers'] ?? [];

        // 检测生效区域
        $areas = $this->resolveAreas($composerJson);

        // 自动分析依赖（从 composer.json require 中筛选 lasaas-module 类型包）
        $dependencies = $this->analyzeDependencies($composerJson);

        // 从 extra 中读取 after 配置
        $after = $composerJson['extra']['lasaas-module']['after'] ?? [];

        // 权重
        $weight = $composerJson['extra']['lasaas-module']['weight'] ?? 0;

        return [
            'package_name' => $packageName,
            'name' => $composerJson['extra']['lasaas-module']['name'] ?? $packageName,
            'description' => $description,
            'version' => $packageInfo['version'],
            'providers' => $providers,
            'weight' => $weight,
            'dependencies' => $dependencies,
            'after' => is_array($after) ? $after : [],
            'areas' => $areas,
            'path' => $path,
            'status' => 'inactive', // 新模块默认不激活，需管理员手动启用
        ];
    }

    /**
     * 解析模块的生效区域。
     *
     * @return string[]
     */
    protected function resolveAreas(array $composerJson): array
    {
        $areas = $composerJson['extra']['lasaas-module']['areas'] ?? [];

        if (is_string($areas)) {
            $areas = [$areas];
        }

        if (empty($areas)) {
            return ['tenant']; // 默认是租户模块
        }

        // 校验
        $validAreas = ['central', 'tenant'];
        $areas = array_intersect($areas, $validAreas);

        return array_values($areas);
    }

    /**
     * 分析模块依赖——从 composer.json require 中筛选同为 lasaas-module 的包。
     *
     * @return string[]
     */
    protected function analyzeDependencies(array $composerJson): array
    {
        $require = $composerJson['require'] ?? [];
        $dependencies = [];

        foreach ($require as $package => $version) {
            // 只筛选已安装的 lasaas-module 类型包
            if ($this->isModulePackage($package)) {
                $dependencies[] = $package;
            }
        }

        return $dependencies;
    }

    /**
     * 判断包名是否为已安装的 lasaas-module 类型包。
     */
    protected function isModulePackage(string $packageName): bool
    {
        return $this->installedModulePackages->has($packageName);
    }

    /**
     * 处理磁盘上已不存在的模块：默认物理删除，--soft 时标记 inactive。
     *
     * 参考 Drupal：模块文件删除 = 模块移除，不应残留数据库记录。
     *
     * @return int 处理的记录数
     */
    protected function markRemovedModules(): int
    {
        $installedNames = $this->installedModulePackages->keys()->all();
        $soft = $this->option('soft');

        // 不管模块当前是什么状态，只要磁盘上不存在就处理
        $removed = Module::whereNotIn('package_name', $installedNames)->get();

        if ($removed->isEmpty()) {
            return 0;
        }

        $count = 0;

        foreach ($removed as $module) {
            if ($this->option('dry-run')) {
                $action = $soft ? 'INACTIVE' : 'DELETE';
                $this->line("  <fg=red>{$action}</> {$module->package_name} (module files removed from disk)");
                $count++;

                continue;
            }

            if ($soft) {
                $module->update(['status' => 'inactive']);
                $this->line("  <fg=red>Marked inactive</> {$module->package_name} (module files removed from disk)");
            } else {
                if ($module->isInstalled()) {
                    $this->warn("  Module '{$module->package_name}' was installed but its files are gone. ".
                        'Run "module:uninstall" before deleting files to allow cleanup.');
                }
                $module->delete();
                $this->line("  <fg=red>Deleted</> {$module->package_name} (module files removed from disk)");
            }

            $count++;
        }

        return $count;
    }
}
