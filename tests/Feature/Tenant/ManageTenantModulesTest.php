<?php

use App\Enums\ModuleStatus;
use App\Livewire\Actions\ManageTenantModules;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\User;
use App\Module\ModuleManager;
use App\Module\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Tenancy;

/**
 * 测试用模块 Provider：租户钩子为空实现，不执行真实迁移。
 */
class TestManageTenantModulesProvider extends ModuleServiceProvider
{
    public function tenantInstall(Tenant $tenant): void {}

    public function tenantOnEnable(Tenant $tenant): void {}

    public function tenantOnDisable(Tenant $tenant): void {}

    public function tenantUninstall(Tenant $tenant): void {}
}

function createTenantAreaModuleForTable(string $packageName = 'test/table-tenant-module'): Module
{
    $module = Module::create([
        'package_name' => $packageName,
        'name' => 'Tenant Module',
        'provider_class' => TestManageTenantModulesProvider::class,
        'path' => base_path('packages/custom/test/table-tenant-module'),
        'areas' => ['central', 'tenant'],
        'status' => ModuleStatus::ACTIVE,
        'installed_at' => now(),
    ]);

    app(ModuleManager::class)->flushCache();

    return $module;
}

function createTableTestTenantModel(): Tenant
{
    Event::fake([TenantCreated::class]);

    return Tenant::create([
        'id' => 'tenant-'.Str::uuid(),
        'name' => 'Test Tenant',
        'user_id' => User::factory()->create()->id,
    ]);
}

function fakeTableTenancyAlreadyInitialized(): Tenancy
{
    $tenancy = new Tenancy;
    $tenancy->initialized = true;

    app()->instance(Tenancy::class, $tenancy);

    return $tenancy;
}

test('the component lists only tenant-area modules', function () {
    $module = createTenantAreaModuleForTable();
    $tenant = createTableTestTenantModel();

    fakeTableTenancyAlreadyInitialized();

    Livewire::test(ManageTenantModules::class, ['record' => $tenant])
        ->assertCanSeeTableRecords([$module]);
});

test('install action installs and enables the module for the tenant', function () {
    $module = createTenantAreaModuleForTable();
    $tenant = createTableTestTenantModel();

    fakeTableTenancyAlreadyInitialized();

    Livewire::test(ManageTenantModules::class, ['record' => $tenant])
        ->callTableAction('install', $module)
        ->assertNotified('模块已安装并启用');

    expect($tenant->tenantModules()->where('module_id', $module->id)->first()->enabled)->toBeTrue();
});

test('disable and enable actions toggle the module', function () {
    $module = createTenantAreaModuleForTable();
    $tenant = createTableTestTenantModel();

    $tenant->setModuleEnabled($module->id, true);

    fakeTableTenancyAlreadyInitialized();

    Livewire::test(ManageTenantModules::class, ['record' => $tenant])
        ->callTableAction('disable', $module)
        ->assertNotified('模块已禁用');

    expect($tenant->tenantModules()->where('module_id', $module->id)->first()->enabled)->toBeFalse();

    Livewire::test(ManageTenantModules::class, ['record' => $tenant])
        ->callTableAction('enable', $module)
        ->assertNotified('模块已启用');

    expect($tenant->tenantModules()->where('module_id', $module->id)->first()->enabled)->toBeTrue();
});

test('uninstall action removes the tenant module record', function () {
    $module = createTenantAreaModuleForTable();
    $tenant = createTableTestTenantModel();

    $tenant->setModuleEnabled($module->id, true);

    fakeTableTenancyAlreadyInitialized();

    Livewire::test(ManageTenantModules::class, ['record' => $tenant])
        ->callTableAction('uninstall', $module);

    expect($tenant->tenantModules()->where('module_id', $module->id)->exists())->toBeFalse();
});
