<?php

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\User;
use App\Module\ModuleBootLoader;
use App\Module\ModuleMigrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Tenancy;

/**
 * 生成一个模块迁移夹具文件（匿名类迁移，建一张只含 id/name 的表）。
 */
function writeModuleMigration(string $dir, string $table, string $stamp, string $subPath = 'database/migrations'): string
{
    $path = $dir.'/'.$subPath.'/'.$stamp.'_create_'.$table.'_table.php';

    File::ensureDirectoryExists(dirname($path), 0755);

    $content = str_replace(
        '__TABLE__',
        $table,
        <<<'PHP'
        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            public function up(): void
            {
                Schema::create('__TABLE__', function (Blueprint $table) {
                    $table->id();
                    $table->string('name')->nullable();
                });
            }

            public function down(): void
            {
                Schema::dropIfExists('__TABLE__');
            }
        };
        PHP,
    );

    File::put($path, $content);

    return $path;
}

/**
 * 生成一个带迁移目录的模块记录。
 */
function createMigrationModule(string $path, string $packageName): Module
{
    return Module::create([
        'package_name' => $packageName,
        'name' => 'Migration Test Module',
        'version' => '1.0.0',
        'providers' => [],
        'weight' => 0,
        'dependencies' => [],
        'after' => [],
        'areas' => ['central', 'tenant'],
        'path' => $path,
        'status' => ModuleStatus::ACTIVE,
    ]);
}

/**
 * 生成一个租户记录（不触发租户库创建）。
 */
function createMigrationTenant(): Tenant
{
    Event::fake([TenantCreated::class]);

    return Tenant::create([
        'id' => 'tenant-'.Str::uuid(),
        'name' => 'Migration Test Tenant',
        'user_id' => User::factory()->create()->id,
    ]);
}

/**
 * 模拟租户上下文已初始化（与现有租户测试一致，跑在当前连接上）。
 */
function fakeMigrationTenancy(): Tenancy
{
    $tenancy = new Tenancy;
    $tenancy->initialized = true;

    app()->instance(Tenancy::class, $tenancy);

    return $tenancy;
}

beforeEach(function () {
    $this->fixtureDir = sys_get_temp_dir().'/lasaas-module-test-'.Str::uuid();
    File::makeDirectory($this->fixtureDir, 0755, true);
});

afterEach(function () {
    File::deleteDirectory($this->fixtureDir);
});

// ---------------------------------------------------------------
// 中央迁移
// ---------------------------------------------------------------

test('migrate runs all module migrations into module_migrations, isolated from system migrations', function () {
    writeModuleMigration($this->fixtureDir, 'mm_alpha', '2026_08_03_100000');
    writeModuleMigration($this->fixtureDir, 'mm_beta', '2026_08_03_100001');

    $module = createMigrationModule($this->fixtureDir, 'test/migration-module');

    app(ModuleMigrationService::class)->migrate($module);

    $rows = DB::table('module_migrations')->where('module_id', $module->id)->get();

    expect($rows)->toHaveCount(2)
        ->and($rows->every(fn ($row): bool => $row->batch === 1))->toBeTrue()
        ->and(Schema::hasTable('mm_alpha'))->toBeTrue()
        ->and(Schema::hasTable('mm_beta'))->toBeTrue();

    // 系统 migrations 表不被污染
    expect(DB::table('migrations')->where('migration', 'like', '%mm_alpha%')->exists())->toBeFalse()
        ->and(DB::table('migrations')->where('migration', 'like', '%mm_beta%')->exists())->toBeFalse();
});

test('migrate on upgrade runs only new migration files as a new batch', function () {
    writeModuleMigration($this->fixtureDir, 'mm_alpha', '2026_08_03_100000');

    $module = createMigrationModule($this->fixtureDir, 'test/migration-module');

    app(ModuleMigrationService::class)->migrate($module);

    writeModuleMigration($this->fixtureDir, 'mm_gamma', '2026_08_03_100002');

    app(ModuleMigrationService::class)->migrate($module);

    $rows = DB::table('module_migrations')->where('module_id', $module->id)->get();

    expect($rows)->toHaveCount(2)
        ->and($rows->first(fn ($row): bool => str_contains($row->migration, 'mm_alpha'))->batch)->toBe(1)
        ->and($rows->first(fn ($row): bool => str_contains($row->migration, 'mm_gamma'))->batch)->toBe(2)
        ->and(Schema::hasTable('mm_gamma'))->toBeTrue();
});

test('rollbackLastBatch undoes only the most recent batch', function () {
    writeModuleMigration($this->fixtureDir, 'mm_alpha', '2026_08_03_100000');

    $module = createMigrationModule($this->fixtureDir, 'test/migration-module');

    app(ModuleMigrationService::class)->migrate($module);

    writeModuleMigration($this->fixtureDir, 'mm_gamma', '2026_08_03_100002');

    app(ModuleMigrationService::class)->migrate($module);

    app(ModuleMigrationService::class)->rollbackLastBatch($module);

    expect(Schema::hasTable('mm_gamma'))->toBeFalse()
        ->and(Schema::hasTable('mm_alpha'))->toBeTrue();

    $rows = DB::table('module_migrations')->where('module_id', $module->id)->get();

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->migration)->toContain('mm_alpha');
});

test('rollback resets all batches and drops all module tables', function () {
    writeModuleMigration($this->fixtureDir, 'mm_alpha', '2026_08_03_100000');

    $module = createMigrationModule($this->fixtureDir, 'test/migration-module');

    app(ModuleMigrationService::class)->migrate($module);

    writeModuleMigration($this->fixtureDir, 'mm_gamma', '2026_08_03_100002');

    app(ModuleMigrationService::class)->migrate($module);

    app(ModuleMigrationService::class)->rollback($module);

    expect(Schema::hasTable('mm_alpha'))->toBeFalse()
        ->and(Schema::hasTable('mm_gamma'))->toBeFalse()
        ->and(DB::table('module_migrations')->where('module_id', $module->id)->exists())->toBeFalse();
});

test('two modules are isolated by module_id with independent batches', function () {
    $dirA = $this->fixtureDir.'/a';
    $dirB = $this->fixtureDir.'/b';
    File::makeDirectory($dirA, 0755, true);
    File::makeDirectory($dirB, 0755, true);

    writeModuleMigration($dirA, 'mm_a_alpha', '2026_08_03_100000');
    writeModuleMigration($dirB, 'mm_b_alpha', '2026_08_03_100000');
    writeModuleMigration($dirB, 'mm_b_beta', '2026_08_03_100001');
    $moduleA = createMigrationModule($dirA, 'test/module-a');
    $moduleB = createMigrationModule($dirB, 'test/module-b');

    app(ModuleMigrationService::class)->migrate($moduleA);
    app(ModuleMigrationService::class)->migrate($moduleB);

    $rowsA = DB::table('module_migrations')->where('module_id', $moduleA->id)->get();
    $rowsB = DB::table('module_migrations')->where('module_id', $moduleB->id)->get();

    expect($rowsA)->toHaveCount(1)
        ->and($rowsB)->toHaveCount(2)
        ->and(DB::table('module_migrations')->distinct()->count('module_id'))->toBe(2)
        ->and($rowsA->first()->batch)->toBe(1)
        ->and($rowsB->pluck('batch')->unique()->values())->toEqual(collect([1]));
});

test('purge clears module_migrations rows even when migration files are deleted', function () {
    writeModuleMigration($this->fixtureDir, 'mm_alpha', '2026_08_03_100000');

    $module = createMigrationModule($this->fixtureDir, 'test/migration-module');

    app(ModuleMigrationService::class)->migrate($module);

    expect(DB::table('module_migrations')->where('module_id', $module->id)->exists())->toBeTrue();

    File::deleteDirectory($this->fixtureDir);

    app(ModuleMigrationService::class)->purge($module);

    expect(DB::table('module_migrations')->where('module_id', $module->id)->exists())->toBeFalse();
});

test('system migrate does not run module migrations', function () {
    writeModuleMigration($this->fixtureDir, 'mm_alpha', '2026_08_03_100000');

    $module = createMigrationModule($this->fixtureDir, 'test/migration-module');

    $this->artisan('migrate')->assertSuccessful();

    expect(Schema::hasTable('mm_alpha'))->toBeFalse()
        ->and(DB::table('module_migrations')->where('module_id', $module->id)->exists())->toBeFalse();
});

test('enable runs central migrations and uninstall rolls them back and purges records', function () {
    writeModuleMigration($this->fixtureDir, 'mm_alpha', '2026_08_03_100000');

    $module = createMigrationModule($this->fixtureDir, 'test/migration-module');

    app(ModuleBootLoader::class)->enable($module);

    expect(Schema::hasTable('mm_alpha'))->toBeTrue()
        ->and(DB::table('module_migrations')->where('module_id', $module->id)->exists())->toBeTrue()
        ->and($module->fresh()->status)->toBe(ModuleStatus::ACTIVE);

    app(ModuleBootLoader::class)->uninstall($module);

    expect(Schema::hasTable('mm_alpha'))->toBeFalse()
        ->and(DB::table('module_migrations')->where('module_id', $module->id)->exists())->toBeFalse()
        ->and(Module::whereKey($module->id)->exists())->toBeFalse();
});

// ---------------------------------------------------------------
// 租户迁移
// ---------------------------------------------------------------

test('migrateForTenant runs module tenant migrations within tenancy context', function () {
    writeModuleMigration($this->fixtureDir, 'mmt_alpha', '2026_08_03_100000', 'database/migrations/tenant');

    $module = createMigrationModule($this->fixtureDir, 'test/tenant-migration-module');
    $tenant = createMigrationTenant();

    fakeMigrationTenancy();

    app(ModuleMigrationService::class)->migrateForTenant($module, $tenant);

    expect(Schema::hasTable('mmt_alpha'))->toBeTrue()
        ->and(DB::table('module_migrations')->where('module_id', $module->id)->exists())->toBeTrue();
});

test('rollbackLastBatchForTenant undoes only the most recent tenant batch', function () {
    writeModuleMigration($this->fixtureDir, 'mmt_alpha', '2026_08_03_100000', 'database/migrations/tenant');

    $module = createMigrationModule($this->fixtureDir, 'test/tenant-migration-module');
    $tenant = createMigrationTenant();

    fakeMigrationTenancy();

    app(ModuleMigrationService::class)->migrateForTenant($module, $tenant);

    writeModuleMigration($this->fixtureDir, 'mmt_gamma', '2026_08_03_100002', 'database/migrations/tenant');

    app(ModuleMigrationService::class)->migrateForTenant($module, $tenant);

    app(ModuleMigrationService::class)->rollbackLastBatchForTenant($module, $tenant);

    expect(Schema::hasTable('mmt_gamma'))->toBeFalse()
        ->and(Schema::hasTable('mmt_alpha'))->toBeTrue();

    $rows = DB::table('module_migrations')->where('module_id', $module->id)->get();

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->migration)->toContain('mmt_alpha');
});

test('enableForTenant and uninstallForTenant run and roll back tenant migrations', function () {
    writeModuleMigration($this->fixtureDir, 'mmt_alpha', '2026_08_03_100000', 'database/migrations/tenant');

    $module = createMigrationModule($this->fixtureDir, 'test/tenant-migration-module');
    $tenant = createMigrationTenant();

    fakeMigrationTenancy();

    app(ModuleBootLoader::class)->enableForTenant($module, $tenant);

    expect(Schema::hasTable('mmt_alpha'))->toBeTrue()
        ->and($tenant->tenantModules()->where('module_id', $module->id)->first()->enabled)->toBeTrue();

    app(ModuleBootLoader::class)->uninstallForTenant($module, $tenant);

    expect(Schema::hasTable('mmt_alpha'))->toBeFalse()
        ->and(DB::table('module_migrations')->where('module_id', $module->id)->exists())->toBeFalse()
        ->and($tenant->tenantModules()->where('module_id', $module->id)->exists())->toBeFalse();
});
