<?php

use App\Filament\Resources\Tenants\Pages\CreateTenant;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Events\TenantCreated;

function createTenantAdmin(): User
{
    $admin = User::factory()->create();

    $role = Role::create(['name' => 'admin']);
    $role->givePermissionTo(Permission::create(['name' => 'ViewAny:Tenant']));
    $role->givePermissionTo(Permission::create(['name' => 'Create:Tenant']));
    $admin->assignRole($role);

    return $admin;
}

function createTenantThroughForm(array $tenantDatabase): Tenant
{
    Event::fake([TenantCreated::class]);

    $admin = createTenantAdmin();

    test()->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Test Shop',
            'email' => 'shop@example.com',
            'user_id' => $admin->id,
            'domains' => [
                ['domain' => 'shop-form.tenant.ddev.site'],
            ],
            'tenantDatabase' => $tenantDatabase,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    return Tenant::where('name', 'Test Shop')->firstOrFail();
}

test('tenant form saves a mariadb manual database configuration', function () {
    $tenant = createTenantThroughForm([
        'connection' => 'mariadb',
        'database' => 'shop_form_db',
        'host' => '10.0.0.9',
        'port' => 3306,
        'username' => 'form_user',
        'password' => 'form-pass',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]);

    expect($tenant->tenantDatabase)->not->toBeNull();
    expect($tenant->tenantDatabase->connection)->toBe('mariadb');
    expect($tenant->tenantDatabase->database)->toBe('shop_form_db');
    expect($tenant->tenantDatabase->host)->toBe('10.0.0.9');
    expect($tenant->tenantDatabase->username)->toBe('form_user');
    expect($tenant->tenantDatabase->password)->toBe('form-pass');
});

test('tenant form saves a pgsql manual database configuration', function () {
    $tenant = createTenantThroughForm([
        'connection' => 'pgsql',
        'database' => 'shop_form_pg',
        'host' => '10.0.0.10',
        'port' => 5432,
        'username' => 'pg_user',
        'password' => 'pg-pass',
        'charset' => 'UTF8',
        'collation' => 'en_US.UTF-8',
    ]);

    expect($tenant->tenantDatabase)->not->toBeNull();
    expect($tenant->tenantDatabase->connection)->toBe('pgsql');
    expect($tenant->tenantDatabase->database)->toBe('shop_form_pg');
    expect($tenant->tenantDatabase->host)->toBe('10.0.0.10');
    expect($tenant->tenantDatabase->port)->toBe(5432);
    expect($tenant->tenantDatabase->username)->toBe('pg_user');
});

test('tenant form saves a sqlite file database configuration', function () {
    $tenant = createTenantThroughForm([
        'connection' => 'sqlite',
        'database' => 'shop_form.sqlite',
    ]);

    expect($tenant->tenantDatabase)->not->toBeNull();
    expect($tenant->tenantDatabase->connection)->toBe('sqlite');
    expect($tenant->tenantDatabase->database)->toBe('shop_form.sqlite');
    expect($tenant->tenantDatabase->host)->toBeNull();
    expect($tenant->tenantDatabase->username)->toBeNull();
    expect($tenant->tenantDatabase->password)->toBeNull();
});

test('tenant form shows per-database-type examples that react to the connection', function () {
    Event::fake([TenantCreated::class]);

    $admin = createTenantAdmin();

    test()->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateTenant::class)
        ->assertSee('数据库类型')
        ->assertSee('port=3306')
        ->fillForm(['tenantDatabase' => ['connection' => 'pgsql']])
        ->assertSee('port=5432')
        ->fillForm(['tenantDatabase' => ['connection' => 'sqlite']])
        ->assertSee('SQLite 使用本地文件数据库')
        ->fillForm(['tenantDatabase' => ['connection' => 'mysql']])
        ->assertSee('port=3306');
});
