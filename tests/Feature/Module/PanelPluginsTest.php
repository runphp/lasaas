<?php

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Module\ModuleDiscoveryManager;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\TenantAdminPanelProvider;
use Filament\Panel;
use Illuminate\Support\Facades\Cache;
use Tests\Fixtures\Modules\PanelExt\Filament\Plugins\AdminBlogPlugin;
use Tests\Fixtures\Modules\PanelExt\Filament\Plugins\TenantBlogPlugin;
use Tests\Fixtures\Modules\PanelExt\PanelExtServiceProvider;

function createPanelExtensionModule(): Module
{
    return Module::create([
        'package_name' => 'test/panel-ext-module',
        'name' => 'Panel Extension Module',
        'providers' => [PanelExtServiceProvider::class],
        'path' => base_path('tests/Fixtures/Modules/PanelExt'),
        'areas' => ['central', 'tenant'],
        'status' => ModuleStatus::ACTIVE,
        'installed_at' => now(),
    ]);
}

beforeEach(function () {
    createPanelExtensionModule();

    app(ModuleDiscoveryManager::class)->flushCache();
    app(ModuleDiscoveryManager::class)->flushPanelPluginsCache();
});

test('admin panel plugins are discovered by convention from central modules', function () {
    $plugins = app(ModuleDiscoveryManager::class)->getAdminPanelPlugins();

    expect($plugins)->toContain(AdminBlogPlugin::class)
        ->and($plugins)->not->toContain(TenantBlogPlugin::class);
});

test('tenant admin panel plugins are discovered by convention from tenant modules', function () {
    $plugins = app(ModuleDiscoveryManager::class)->getTenantAdminPanelPlugins();

    expect($plugins)->toContain(TenantBlogPlugin::class)
        ->and($plugins)->not->toContain(AdminBlogPlugin::class);
});

test('admin panel plugins resolve without cache when cache store is unavailable', function () {
    Cache::shouldReceive('rememberForever')
        ->once()
        ->andThrow(new RuntimeException('cache table missing'));

    $plugins = app(ModuleDiscoveryManager::class)->getAdminPanelPlugins();

    expect($plugins)->toContain(AdminBlogPlugin::class)
        ->and($plugins)->not->toContain(TenantBlogPlugin::class);
});

test('admin panel registers module admin panel plugins', function () {
    $panel = (new AdminPanelProvider(app()))->panel(Panel::make());

    expect($panel->getPlugins())->toHaveKey((new AdminBlogPlugin)->getId());
});

test('tenant admin panel registers module tenant admin panel plugins', function () {
    $panel = (new TenantAdminPanelProvider(app()))->panel(Panel::make());

    expect($panel->getPlugins())->toHaveKey((new TenantBlogPlugin)->getId());
});
