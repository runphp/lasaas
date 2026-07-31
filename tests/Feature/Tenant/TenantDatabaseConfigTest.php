<?php

use App\Models\Tenant;
use App\Models\TenantDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Event;

function createTestTenant(string $id): Tenant
{
    return Tenant::create([
        'id' => $id,
        'name' => 'Test Shop',
        'user_id' => User::factory()->create()->id,
    ]);
}

test('setDatabaseConnection stores the manual database configuration', function () {
    Event::fake();

    $tenant = createTestTenant('shop-001');

    $tenant->setDatabaseConnection([
        'database' => 'shop_db_001',
        'connection' => 'mariadb',
        'host' => '10.0.0.5',
        'port' => 3307,
        'username' => 'shop_user',
        'password' => 'secret-password',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]);

    $tenant->refresh();

    expect(TenantDatabase::find('shop-001'))->not->toBeNull();
    expect($tenant->tenantDatabase->database)->toBe('shop_db_001');
});

test('tenant database config is read from the tenant_databases table', function () {
    Event::fake();

    $tenant = createTestTenant('shop-002');
    $tenant->setDatabaseConnection([
        'database' => 'shop_db_002',
        'connection' => 'mariadb',
        'host' => '10.0.0.6',
        'port' => 3306,
        'username' => 'shop_user',
        'password' => 'secret-password',
    ]);

    $tenant->refresh();

    $config = $tenant->database();

    expect($config->getName())->toBe('shop_db_002');
    expect($config->getUsername())->toBe('shop_user');
    expect($config->getPassword())->toBe('secret-password');
    expect($config->getTemplateConnectionName())->toBe('mariadb');

    $connection = $config->connection();

    expect($connection['database'])->toBe('shop_db_002');
    expect($connection['host'])->toBe('10.0.0.6');
    expect($connection['port'])->toBe(3306);
    expect($connection['username'])->toBe('shop_user');
    expect($connection['password'])->toBe('secret-password');
});

test('tenant database config supports pgsql connections', function () {
    Event::fake();

    $tenant = createTestTenant('shop-005');
    $tenant->setDatabaseConnection([
        'database' => 'shop_db_005',
        'connection' => 'pgsql',
        'host' => '10.0.0.8',
        'port' => 5432,
        'username' => 'pg_user',
        'password' => 'secret-password',
        'charset' => 'UTF8',
        'collation' => 'en_US.UTF-8',
    ]);

    $config = $tenant->database();
    $connection = $config->connection();

    expect($config->getTemplateConnectionName())->toBe('pgsql');
    expect($connection['driver'])->toBe('pgsql');
    expect($connection['database'])->toBe('shop_db_005');
    expect($connection['host'])->toBe('10.0.0.8');
    expect($connection['port'])->toBe(5432);
    expect($connection['username'])->toBe('pg_user');
    expect($connection['password'])->toBe('secret-password');
});

test('tenant database config supports sqlite file connections', function () {
    Event::fake();

    $tenant = createTestTenant('shop-006');
    $tenant->setDatabaseConnection([
        'database' => 'shop_db_006.sqlite',
        'connection' => 'sqlite',
    ]);

    $config = $tenant->database();
    $connection = $config->connection();

    expect($config->getTemplateConnectionName())->toBe('sqlite');
    expect($connection['driver'])->toBe('sqlite');
    expect($connection['database'])->toBe(database_path('shop_db_006.sqlite'));
});

test('creating a tenant does not auto configure its database', function () {
    Event::fake();

    $tenant = createTestTenant('shop-003');

    $tenant->refresh();

    expect(TenantDatabase::where('tenant_id', 'shop-003')->exists())->toBeFalse();
    expect($tenant->tenantDatabase)->toBeNull();
});

test('tenant without a manual database config cannot build a connection', function () {
    Event::fake();

    $tenant = createTestTenant('shop-007');

    expect(fn () => $tenant->database()->connection())
        ->toThrow(RuntimeException::class, '未配置数据库连接');
});

test('deleting a tenant removes the config record but keeps the actual database', function () {
    Event::fake();

    $tenant = createTestTenant('shop-004');
    $tenant->setDatabaseConnection(['connection' => 'mariadb', 'database' => 'shop_db_004']);

    $tenant->delete();

    expect(TenantDatabase::find('shop-004'))->toBeNull();
});
