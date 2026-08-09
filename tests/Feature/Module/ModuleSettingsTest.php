<?php

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\User;
use App\Module\ModuleServiceProvider;
use App\Module\ModuleSettingManager;
use App\Module\Settings\ModulePlatformSettings;
use App\Module\Settings\ModuleTenantSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Stancl\Tenancy\Events\TenantCreated;

/**
 * 测试用模块平台设置类：PHP 默认值即默认设置。
 */
class TestPlatformSettings extends ModulePlatformSettings
{
    public int $per_page = 10;

    public bool $allow_comments = false;

    public static function groupKey(): string
    {
        return 'test/settings-module';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('per_page')->default(10),
            Toggle::make('allow_comments')->default(false),
        ];
    }
}

/**
 * 测试用模块租户设置类：按租户隔离，group 为 tenant_module:{tenant_id}:{groupKey}。
 */
class TestTenantSettings extends ModuleTenantSettings
{
    public int $per_page = 5;

    public string $accent_color = '#6366f1';

    public bool $show_excerpt = true;

    public static function groupKey(): string
    {
        return 'test/settings-module';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('per_page')->default(5),
            TextInput::make('accent_color')->default('#6366f1'),
            Toggle::make('show_excerpt')->default(true),
        ];
    }
}

/**
 * 测试用模块 Provider：声明中央/租户设置类，表单结构由设置类 schema() 提供。
 */
class TestSettingsModuleProvider extends ModuleServiceProvider
{
    public function settingsClasses(): array
    {
        return [
            'platform' => TestPlatformSettings::class,
            'tenant' => TestTenantSettings::class,
        ];
    }
}

/**
 * 测试用模块 Provider：不声明设置类，模拟无需设置功能的模块。
 */
class TestNoSettingsModuleProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        $this->app['config']->set('test_nosettings', [
            'per_page' => 10,
        ]);
    }
}

function createSettingsModule(string $providerClass): Module
{
    return Module::create([
        'package_name' => $providerClass === TestSettingsModuleProvider::class ? 'test/settings-module' : 'test/nosettings-module',
        'name' => 'Settings Module',
        'providers' => [$providerClass],
        'path' => base_path('packages/custom/test/settings-module'),
        'areas' => ['central', 'tenant'],
        'status' => ModuleStatus::ACTIVE,
        'installed_at' => now(),
    ]);
}

function createSettingsTestTenant(): Tenant
{
    Event::fake([TenantCreated::class]);

    return Tenant::create([
        'id' => 'tenant-'.Str::uuid(),
        'name' => 'Test Tenant',
        'user_id' => User::factory()->create()->id,
    ]);
}

test('central settings resolve with the settings class defaults when nothing is stored', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);

    $settings = app(ModuleSettingManager::class)->resolvePlatformSettings($module);

    expect($settings)->toBeInstanceOf(TestPlatformSettings::class)
        ->and($settings->per_page)->toBe(10)
        ->and($settings->allow_comments)->toBeFalse()
        ->and(DB::table('settings')->where('group', 'module:test/settings-module')->count())->toBe(0);
});

test('saving central settings persists to the central settings table and reloads', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);
    $manager = app(ModuleSettingManager::class);

    $manager->resolvePlatformSettings($module)?->fill([
        'per_page' => 3,
        'allow_comments' => true,
    ])->save();

    expect(DB::table('settings')->where('group', 'module:test/settings-module')->get())
        ->toHaveCount(2);

    $reloaded = $manager->resolvePlatformSettings($module);

    expect($reloaded->per_page)->toBe(3)
        ->and($reloaded->allow_comments)->toBeTrue();
});

test('tenant settings are isolated per tenant', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);
    $tenantA = createSettingsTestTenant();
    $tenantB = createSettingsTestTenant();

    $tenantA->setModuleEnabled($module->id, true);

    $manager = app(ModuleSettingManager::class);

    $manager->resolveTenantSettings($module, $tenantA)?->fill([
        'per_page' => 2,
        'accent_color' => '#ff0000',
    ])->save();

    $settingsA = $manager->resolveTenantSettings($module, $tenantA);
    $settingsB = $manager->resolveTenantSettings($module, $tenantB);

    expect($settingsA->per_page)->toBe(2)
        ->and($settingsA->accent_color)->toBe('#ff0000')
        ->and($settingsB->per_page)->toBe(10)
        ->and($settingsB->accent_color)->toBe('#6366f1')
        ->and($settingsB->show_excerpt)->toBeTrue();

    expect(DB::table('settings')->where('group', "tenant_module:{$tenantA->getTenantKey()}:test/settings-module")->count())->toBe(2)
        ->and(DB::table('settings')->where('group', "tenant_module:{$tenantB->getTenantKey()}:test/settings-module")->count())->toBe(0);
});

test('tenant settings do not overwrite the central settings group', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);
    $tenant = createSettingsTestTenant();

    $manager = app(ModuleSettingManager::class);

    $manager->resolvePlatformSettings($module)?->fill(['per_page' => 3])->save();
    $manager->resolveTenantSettings($module, $tenant)?->fill(['per_page' => 9])->save();

    expect($manager->resolvePlatformSettings($module)->per_page)->toBe(3)
        ->and($manager->resolveTenantSettings($module, $tenant)->per_page)->toBe(9);
});

test('tenant settings fall back to module central values for same-name fields', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);
    $tenant = createSettingsTestTenant();
    $manager = app(ModuleSettingManager::class);

    $manager->resolvePlatformSettings($module)?->fill(['per_page' => 7])->save();

    $settings = $manager->resolveTenantSettings($module, $tenant);

    expect($settings->per_page)->toBe(7)
        ->and($settings->accent_color)->toBe('#6366f1')
        ->and($settings->show_excerpt)->toBeTrue();
});

test('tenant override wins over the module central same-name value', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);
    $tenant = createSettingsTestTenant();
    $manager = app(ModuleSettingManager::class);

    $manager->resolvePlatformSettings($module)?->fill(['per_page' => 7])->save();
    $manager->resolveTenantSettings($module, $tenant)?->fill(['per_page' => 15])->save();

    expect($manager->resolveTenantSettings($module, $tenant)->per_page)->toBe(15);
});

test('saving tenant settings does not persist fallback module central values', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);
    $tenant = createSettingsTestTenant();
    $manager = app(ModuleSettingManager::class);

    $manager->resolvePlatformSettings($module)?->fill(['per_page' => 7])->save();

    $manager->resolveTenantSettings($module, $tenant)?->fill([
        'per_page' => 7,
        'accent_color' => '#ff0000',
        'show_excerpt' => true,
    ])->save();

    $stored = DB::table('settings')
        ->where('group', "tenant_module:{$tenant->getTenantKey()}:test/settings-module")
        ->pluck('name');

    expect($stored)->toHaveCount(1)
        ->and($stored)->toContain('accent_color')
        ->and($stored)->not->toContain('per_page')
        ->and($stored)->not->toContain('show_excerpt');

    $manager->resolvePlatformSettings($module)?->fill(['per_page' => 20])->save();

    expect($manager->resolveTenantSettings($module, $tenant)->per_page)->toBe(20);
});

test('central and tenant settings classes and schemas are forwarded to the module provider', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);

    $manager = app(ModuleSettingManager::class);

    expect($manager->platformSettingsClass($module))->toBe(TestPlatformSettings::class)
        ->and($manager->tenantSettingsClass($module))->toBe(TestTenantSettings::class)
        ->and($manager->platformSettingsSchema($module))->toHaveCount(2)
        ->and($manager->tenantSettingsSchema($module))->toHaveCount(3);
});

test('settings resolution returns null for providers that declare no settings class', function () {
    $module = createSettingsModule(TestNoSettingsModuleProvider::class);

    $manager = app(ModuleSettingManager::class);

    expect($manager->platformSettingsClass($module))->toBeNull()
        ->and($manager->tenantSettingsClass($module))->toBeNull()
        ->and($manager->resolvePlatformSettings($module))->toBeNull()
        ->and($manager->resolveTenantSettings($module))->toBeNull()
        ->and($manager->platformSettingsSchema($module))->toBe([])
        ->and($manager->tenantSettingsSchema($module))->toBe([]);
});

test('uninstalling a module deletes its central and tenant settings', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);
    $tenant = createSettingsTestTenant();
    $manager = app(ModuleSettingManager::class);

    $manager->resolvePlatformSettings($module)?->fill(['per_page' => 3])->save();
    $manager->resolveTenantSettings($module, $tenant)?->fill(['per_page' => 9])->save();

    $manager->deleteSettingsFor($module);

    expect(DB::table('settings')->where('group', 'module:test/settings-module')->count())->toBe(0)
        ->and(DB::table('settings')->where('group', 'like', 'tenant_module:%:test/settings-module')->count())->toBe(0);
});

test('uninstalling a module for one tenant deletes only that tenant settings', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);
    $tenantA = createSettingsTestTenant();
    $tenantB = createSettingsTestTenant();
    $manager = app(ModuleSettingManager::class);

    $manager->resolveTenantSettings($module, $tenantA)?->fill(['per_page' => 9])->save();
    $manager->resolveTenantSettings($module, $tenantB)?->fill(['per_page' => 8])->save();

    $manager->deleteSettingsFor($module, $tenantA);

    expect(DB::table('settings')->where('group', "tenant_module:{$tenantA->getTenantKey()}:test/settings-module")->count())->toBe(0)
        ->and(DB::table('settings')->where('group', "tenant_module:{$tenantB->getTenantKey()}:test/settings-module")->count())->toBe(1);
});
