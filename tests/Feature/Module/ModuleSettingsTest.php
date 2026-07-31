<?php

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\User;
use App\Module\ModuleManager;
use App\Module\ModuleServiceProvider;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Stancl\Tenancy\Events\TenantCreated;

/**
 * 测试用模块 Provider：声明 configKey 与两套设置 schema（含默认值）。
 * 默认配置由框架从 schema 提取，模块无需提供 config 文件。
 */
class TestSettingsModuleProvider extends ModuleServiceProvider
{
    public function configKey(): string
    {
        return 'test_settings';
    }

    public function centralSettingsSchema(): array
    {
        return [
            TextInput::make('per_page')->default(10),
            Toggle::make('allow_comments')->default(false),
        ];
    }

    public function tenantSettingsSchema(): array
    {
        return [
            TextInput::make('per_page')->default(5),
            TextInput::make('accent_color')->default('#6366f1'),
            Toggle::make('show_excerpt')->default(true),
        ];
    }
}

/**
 * 测试用模块 Provider：不声明 configKey，模拟无需运行时配置合并的模块。
 */
class TestNoConfigModuleProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        $this->app['config']->set('test_noconfig', [
            'per_page' => 10,
        ]);
    }
}

function createSettingsModule(string $providerClass, array $settings = []): Module
{
    return Module::create([
        'package_name' => $providerClass === TestSettingsModuleProvider::class ? 'test/settings-module' : 'test/noconfig-module',
        'name' => 'Settings Module',
        'provider_class' => $providerClass,
        'path' => base_path('packages/custom/test/settings-module'),
        'areas' => ['central', 'tenant'],
        'status' => ModuleStatus::ACTIVE,
        'installed_at' => now(),
        'settings' => $settings,
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

test('default config is built from the settings schemas, central defaults win', function () {
    createSettingsModule(TestSettingsModuleProvider::class);

    $manager = app(ModuleManager::class);
    $manager->flushCache();
    $manager->loadCentralModules();

    expect(config('test_settings.per_page'))->toBe(10)
        ->and(config('test_settings.allow_comments'))->toBeFalse()
        ->and(config('test_settings.accent_color'))->toBe('#6366f1')
        ->and(config('test_settings.show_excerpt'))->toBeTrue();
});

test('central settings merge onto the schema defaults', function () {
    createSettingsModule(TestSettingsModuleProvider::class, [
        'per_page' => 3,
        'allow_comments' => true,
    ]);

    $manager = app(ModuleManager::class);
    $manager->flushCache();
    $manager->loadCentralModules();

    expect(config('test_settings.per_page'))->toBe(3)
        ->and(config('test_settings.allow_comments'))->toBeTrue()
        ->and(config('test_settings.accent_color'))->toBe('#6366f1');
});

test('tenant settings override central settings', function () {
    createSettingsModule(TestSettingsModuleProvider::class, [
        'per_page' => 3,
        'allow_comments' => true,
    ]);

    $tenant = createSettingsTestTenant();
    $tenant->setModuleEnabled(Module::where('package_name', 'test/settings-module')->first()->id, true);
    $tenant->tenantModules()->first()->update([
        'settings' => ['per_page' => 2, 'accent_color' => '#ff0000'],
    ]);

    $manager = app(ModuleManager::class);
    $manager->flushCache();
    $manager->loadCentralModules();
    $manager->loadTenantModules($tenant);

    expect(config('test_settings.per_page'))->toBe(2)
        ->and(config('test_settings.accent_color'))->toBe('#ff0000')
        ->and(config('test_settings.allow_comments'))->toBeTrue();
});

test('settings are not merged when the provider declares no configKey', function () {
    createSettingsModule(TestNoConfigModuleProvider::class, [
        'per_page' => 3,
    ]);

    $manager = app(ModuleManager::class);
    $manager->flushCache();
    $manager->loadCentralModules();

    expect(config('test_noconfig.per_page'))->toBe(10);
});

test('central and tenant settings schemas are forwarded to the module provider', function () {
    $module = createSettingsModule(TestSettingsModuleProvider::class);

    $manager = app(ModuleManager::class);

    expect($manager->centralSettingsSchema($module))->toHaveCount(2)
        ->and($manager->tenantSettingsSchema($module))->toHaveCount(3);
});

test('settings schemas are empty for providers that declare none', function () {
    $module = createSettingsModule(TestNoConfigModuleProvider::class);

    $manager = app(ModuleManager::class);

    expect($manager->centralSettingsSchema($module))->toBe([])
        ->and($manager->tenantSettingsSchema($module))->toBe([]);
});
