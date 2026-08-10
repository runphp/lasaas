<?php

use App\Enums\MenuPosition;
use App\Enums\ModuleStatus;
use App\Events\FrontendDashboardCardsCollecting;
use App\Menu\NavItem;
use App\Menu\SidebarMenu;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Tenancy;

function createNavModule(): Module
{
    return Module::create([
        'package_name' => 'lasaas/address',
        'name' => 'Address',
        'providers' => [],
        'path' => base_path('packages/custom/lasaas/address'),
        'areas' => ['central', 'tenant'],
        'status' => ModuleStatus::ACTIVE,
        'installed_at' => now(),
    ]);
}

function createNavTenant(): Tenant
{
    Event::fake([TenantCreated::class]);

    return Tenant::create([
        'id' => 'tenant-'.Str::uuid(),
        'name' => 'Test Shop',
        'user_id' => User::factory()->create()->id,
        'status' => 'active',
    ]);
}

test('sidebar menu builds with built-in items', function () {
    URL::defaults(['current_team' => 'acme', 'team' => 'acme']);

    $menu = app(SidebarMenu::class)->for(MenuPosition::DashboardNav);

    $items = iterator_to_array($menu);

    expect($items)->toHaveCount(2)
        ->and($items[0]->text())->toBe('Dashboard')
        ->and($items[0]->getGroup())->toBe('Team')
        ->and($items[1]->text())->toBe('Settings')
        ->and($items[1]->getGroup())->toBe('Personal');
});

test('tenant sidebar menu builds with tenant built-in items', function () {
    URL::defaults(['current_team' => 'acme', 'team' => 'acme']);

    $menu = app(SidebarMenu::class)->for(MenuPosition::TenantNav);

    $items = iterator_to_array($menu);

    expect($items)->toHaveCount(2)
        ->and($items[0]->text())->toBe('Dashboard')
        ->and($items[0]->getGroup())->toBe('Team')
        ->and($items[1]->text())->toBe('Settings')
        ->and($items[1]->getGroup())->toBe('Personal');
});

test('sidebar menu registers module nav items into groups', function () {
    URL::defaults(['current_team' => 'acme', 'team' => 'acme']);

    app(SidebarMenu::class)->register(MenuPosition::DashboardNav, function ($menu): void {
        $menu->add(NavItem::to('/team-module', '团队模块')->icon('users')->group('Team'));
        $menu->add(NavItem::to('/personal-module', '个人模块')->icon('user')->group('Personal'));
    });

    $menu = app(SidebarMenu::class)->for(MenuPosition::DashboardNav);
    $items = iterator_to_array($menu);

    expect($items)->toHaveCount(4)
        ->and($items[2]->text())->toBe('团队模块')
        ->and($items[2]->getGroup())->toBe('Team')
        ->and($items[3]->text())->toBe('个人模块')
        ->and($items[3]->getGroup())->toBe('Personal');
});

test('central sidebar includes module nav items without tenancy context', function () {
    URL::defaults(['current_team' => 'acme', 'team' => 'acme']);

    $module = createNavModule();

    $nav = app(SidebarMenu::class);
    $nav->forModule($module->package_name, function () use ($nav): void {
        $nav->register(MenuPosition::DashboardNav, function ($menu): void {
            $menu->add(NavItem::to('/addresses', '地址')->icon('map-pin')->group('Personal'));
        });
    });

    $items = iterator_to_array($nav->for(MenuPosition::DashboardNav));

    expect($items)->toHaveCount(3)
        ->and($items[2]->text())->toBe('地址');
});

test('tenant sidebar excludes nav items from modules not enabled for the tenant', function () {
    URL::defaults(['current_team' => 'acme', 'team' => 'acme']);

    $module = createNavModule();
    $tenant = createNavTenant();

    $nav = app(SidebarMenu::class);
    $nav->forModule($module->package_name, function () use ($nav): void {
        $nav->register(MenuPosition::TenantNav, function ($menu): void {
            $menu->add(NavItem::to('/tenant-addresses', '地址')->icon('map-pin')->group('Personal'));
        });
    });

    $tenancy = app(Tenancy::class);
    $tenancy->tenant = $tenant;
    $tenancy->initialized = true;

    $items = iterator_to_array($nav->for(MenuPosition::TenantNav));

    expect($items)->toHaveCount(2)
        ->and($items[0]->text())->toBe('Dashboard')
        ->and($items[1]->text())->toBe('Settings');
});

test('tenant sidebar includes nav items from modules enabled for the tenant', function () {
    URL::defaults(['current_team' => 'acme', 'team' => 'acme']);

    $module = createNavModule();
    $tenant = createNavTenant();
    $tenant->setModuleEnabled($module->id, true);

    $nav = app(SidebarMenu::class);
    $nav->forModule($module->package_name, function () use ($nav): void {
        $nav->register(MenuPosition::TenantNav, function ($menu): void {
            $menu->add(NavItem::to('/tenant-addresses', '地址')->icon('map-pin')->group('Personal'));
        });
    });

    $tenancy = app(Tenancy::class);
    $tenancy->tenant = $tenant;
    $tenancy->initialized = true;

    $items = iterator_to_array($nav->for(MenuPosition::TenantNav));

    expect($items)->toHaveCount(3)
        ->and($items[2]->text())->toBe('地址');
});

test('sidebar menu is idempotent across builds', function () {
    URL::defaults(['current_team' => 'acme', 'team' => 'acme']);

    $menu = app(SidebarMenu::class)->for(MenuPosition::DashboardNav);

    expect(app(SidebarMenu::class)->for(MenuPosition::DashboardNav))->toBe($menu);
});

test('sidebar merges module items into the team and personal groups', function () {
    $user = User::factory()->create();

    app(SidebarMenu::class)->register(MenuPosition::DashboardNav, function ($menu): void {
        $menu->add(NavItem::to('/team-module', '团队模块')->icon('users')->group('Team'));
        $menu->add(NavItem::to('/personal-module', '个人模块')->icon('user')->group('Personal'));
    });

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('团队模块')
        ->assertSee('个人模块');
});

test('sidebar nav component groups items into team, personal and dynamic groups', function () {
    URL::defaults(['current_team' => 'acme', 'team' => 'acme']);

    app(SidebarMenu::class)->register(MenuPosition::DashboardNav, function ($menu): void {
        $menu->add(NavItem::to('/team-module', '团队模块')->icon('users')->group('Team'));
        $menu->add(NavItem::to('/personal-module', '个人模块')->icon('user')->group('Personal'));
        $menu->add(NavItem::to('/content-module', '内容模块')->icon('document-text')->group('内容'));
        $menu->add(NavItem::to('/solo-module', '独立模块')->icon('link'));
    });

    Livewire::test('nav.sidebar-nav', ['position' => MenuPosition::DashboardNav->value])
        ->assertSet('groups.0.heading', 'Team')
        ->assertSet('groups.0.grid', true)
        ->assertSet('groups.0.items.0.text', 'Dashboard')
        ->assertSet('groups.0.items.1.text', '团队模块')
        ->assertSet('groups.1.heading', 'Personal')
        ->assertSet('groups.1.grid', true)
        ->assertSet('groups.1.items.0.text', 'Settings')
        ->assertSet('groups.1.items.1.text', '个人模块')
        ->assertSet('groups.2.heading', '内容')
        ->assertSet('groups.2.grid', false)
        ->assertSet('groups.2.items.0.text', '内容模块')
        ->assertSet('groups.3.heading', null)
        ->assertSet('groups.3.grid', false)
        ->assertSet('groups.3.items.0.text', '独立模块')
        ->assertSee('团队模块')
        ->assertSee('个人模块')
        ->assertSee('内容模块')
        ->assertSee('独立模块');
});

test('frontend dashboard cards event collects module cards', function () {
    Event::listen(FrontendDashboardCardsCollecting::class, function (FrontendDashboardCardsCollecting $event): void {
        $event->items->push(['title' => '博客', 'description' => '发布文章', 'url' => '/blog']);
    });

    $cards = new Collection;

    event(new FrontendDashboardCardsCollecting($cards, 'tenant'));

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['title'])->toBe('博客')
        ->and($cards[0]['description'])->toBe('发布文章');
});

test('module.enabled middleware alias is registered', function () {
    expect(app()->make(Router::class)->getMiddleware())->toHaveKey('module.enabled');
});
