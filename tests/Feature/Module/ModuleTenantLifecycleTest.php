<?php

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\User;
use App\Module\Http\Middleware\EnsureModuleEnabled;
use App\Module\ModuleBootLoader;
use App\Module\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 测试用模块 Provider：记录租户钩子调用顺序，不执行真实迁移。
 */
class TestTenantModuleProvider extends ModuleServiceProvider
{
    public static array $calls = [];

    public static function reset(): void
    {
        self::$calls = [];
    }

    public function tenantInstall(Tenant $tenant): void
    {
        self::$calls[] = 'tenantInstall';
    }

    public function tenantOnEnable(Tenant $tenant): void
    {
        self::$calls[] = 'tenantOnEnable';
    }

    public function tenantOnDisable(Tenant $tenant): void
    {
        self::$calls[] = 'tenantOnDisable';
    }

    public function tenantUninstall(Tenant $tenant): void
    {
        self::$calls[] = 'tenantUninstall';
    }
}

function createTenantAreaModule(string $packageName = 'test/tenant-module'): Module
{
    return Module::create([
        'package_name' => $packageName,
        'name' => 'Tenant Module',
        'providers' => [TestTenantModuleProvider::class],
        'path' => base_path('packages/custom/test/tenant-module'),
        'areas' => ['central', 'tenant'],
        'status' => ModuleStatus::ACTIVE,
        'installed_at' => now(),
    ]);
}

function createTestTenantModel(): Tenant
{
    Event::fake([TenantCreated::class]);

    return Tenant::create([
        'id' => 'tenant-'.Str::uuid(),
        'name' => 'Test Tenant',
        'user_id' => User::factory()->create()->id,
    ]);
}

function fakeTenancyAlreadyInitialized(): Tenancy
{
    $tenancy = new Tenancy;
    $tenancy->initialized = true;

    app()->instance(Tenancy::class, $tenancy);

    return $tenancy;
}

test('enableForTenant creates an enabled tenant_modules record and runs install hooks', function () {
    TestTenantModuleProvider::reset();

    $module = createTenantAreaModule();
    $tenant = createTestTenantModel();

    fakeTenancyAlreadyInitialized();

    app(ModuleBootLoader::class)->enableForTenant($module, $tenant);

    $tenantModule = $tenant->tenantModules()->where('module_id', $module->id)->first();

    expect($tenantModule)->not->toBeNull()
        ->and($tenantModule->enabled)->toBeTrue()
        ->and(TestTenantModuleProvider::$calls)->toBe(['tenantInstall', 'tenantOnEnable']);
});

test('enableForTenant on an existing record only runs onEnable', function () {
    TestTenantModuleProvider::reset();

    $module = createTenantAreaModule();
    $tenant = createTestTenantModel();

    $tenant->setModuleEnabled($module->id, false);

    fakeTenancyAlreadyInitialized();

    app(ModuleBootLoader::class)->enableForTenant($module, $tenant);

    expect($tenant->tenantModules()->where('module_id', $module->id)->first()->enabled)->toBeTrue()
        ->and(TestTenantModuleProvider::$calls)->toBe(['tenantOnEnable']);
});

test('disableForTenant disables the module and runs the disable hook', function () {
    TestTenantModuleProvider::reset();

    $module = createTenantAreaModule();
    $tenant = createTestTenantModel();

    $tenant->setModuleEnabled($module->id, true);

    fakeTenancyAlreadyInitialized();

    app(ModuleBootLoader::class)->disableForTenant($module, $tenant);

    expect($tenant->tenantModules()->where('module_id', $module->id)->first()->enabled)->toBeFalse()
        ->and(TestTenantModuleProvider::$calls)->toBe(['tenantOnDisable']);
});

test('uninstallForTenant runs the uninstall hook and deletes the record', function () {
    TestTenantModuleProvider::reset();

    $module = createTenantAreaModule();
    $tenant = createTestTenantModel();

    $tenant->setModuleEnabled($module->id, true);

    fakeTenancyAlreadyInitialized();

    app(ModuleBootLoader::class)->uninstallForTenant($module, $tenant);

    expect($tenant->tenantModules()->where('module_id', $module->id)->exists())->toBeFalse()
        ->and(TestTenantModuleProvider::$calls)->toBe(['tenantUninstall']);
});

test('EnsureModuleEnabled passes when the module is enabled for the tenant', function () {
    $module = createTenantAreaModule();
    $tenant = createTestTenantModel();

    $tenant->setModuleEnabled($module->id, true);

    $tenancy = fakeTenancyAlreadyInitialized();
    $tenancy->tenant = $tenant;

    $response = (new EnsureModuleEnabled)->handle(
        request(),
        fn (): Response => new Response('ok'),
        $module->package_name,
    );

    expect($response->getStatusCode())->toBe(200);
});

test('EnsureModuleEnabled returns 404 when the module is not enabled for the tenant', function () {
    $module = createTenantAreaModule();
    $tenant = createTestTenantModel();

    fakeTenancyAlreadyInitialized()->tenant = $tenant;

    $middleware = new EnsureModuleEnabled;

    expect(fn () => $middleware->handle(
        request(),
        fn (): Response => new Response('ok'),
        $module->package_name,
    ))->toThrow(NotFoundHttpException::class);
});

test('EnsureModuleEnabled returns 404 when no tenant is identified', function () {
    $module = createTenantAreaModule();

    fakeTenancyAlreadyInitialized();

    $middleware = new EnsureModuleEnabled;

    expect(fn () => $middleware->handle(
        request(),
        fn (): Response => new Response('ok'),
        $module->package_name,
    ))->toThrow(NotFoundHttpException::class);
});

test('central disable makes the module unavailable to all tenants despite enabled flags', function () {
    $module = createTenantAreaModule();
    $tenant = createTestTenantModel();

    $tenant->setModuleEnabled($module->id, true);

    expect($tenant->getEnabledModules())->toContain($module->package_name);

    $module->update(['status' => ModuleStatus::INACTIVE]);

    expect($tenant->getEnabledModules())->not->toContain($module->package_name);
});

test('EnsureModuleEnabled returns 404 when the module was disabled in the central app', function () {
    $module = createTenantAreaModule();
    $tenant = createTestTenantModel();

    $tenant->setModuleEnabled($module->id, true);
    $module->update(['status' => ModuleStatus::INACTIVE]);

    fakeTenancyAlreadyInitialized()->tenant = $tenant;

    $middleware = new EnsureModuleEnabled;

    expect(fn () => $middleware->handle(
        request(),
        fn (): Response => new Response('ok'),
        $module->package_name,
    ))->toThrow(NotFoundHttpException::class);
});
