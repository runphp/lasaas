<?php

use App\Models\TenantDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Stancl\Tenancy\Jobs\MigrateDatabase;

function createTenantWithDatabase(array $attributes): TenantDatabase
{
    $tenant = createTestTenant('shop-'.Str::uuid());

    $tenant->setDatabaseConnection([
        'connection' => 'mariadb',
        'database' => 'shop_db',
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'shop_user',
        'password' => 'secret',
        ...$attributes,
    ]);

    return $tenant->tenantDatabase;
}

test('changing schema-affecting database settings runs tenant migrations', function () {
    Bus::fake();

    $tenantDatabase = createTenantWithDatabase(['prefix' => 'v1_']);

    $tenantDatabase->update([
        'database' => 'shop_db_v2',
        'prefix' => 'v2_',
    ]);

    Bus::assertDispatched(MigrateDatabase::class);
});

test('changing the connection also triggers tenant migrations', function () {
    Bus::fake();

    $tenantDatabase = createTenantWithDatabase([]);

    $tenantDatabase->update(['connection' => 'pgsql']);

    Bus::assertDispatched(MigrateDatabase::class);
});

test('changing host or port does not trigger tenant migrations', function () {
    Bus::fake();

    $tenantDatabase = createTenantWithDatabase([]);

    $tenantDatabase->update(['host' => '10.0.0.99', 'port' => 3307]);

    Bus::assertNotDispatched(MigrateDatabase::class);
});
