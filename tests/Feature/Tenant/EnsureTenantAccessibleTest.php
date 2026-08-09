<?php

use App\Enums\TenantStatus;
use App\Http\Middleware\EnsureTenantAccessible;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Tenancy;
use Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

function createAccessibleTenant(TenantStatus $status, ?string $databaseFile = null): Tenant
{
    Event::fake();

    $tenant = Tenant::create([
        'id' => 'tenant-'.Str::uuid(),
        'name' => 'Test Shop',
        'user_id' => User::factory()->create()->id,
        'status' => $status,
    ]);

    if ($databaseFile !== null) {
        $tenant->tenantDatabase()->create([
            'connection' => 'sqlite',
            'database' => $databaseFile,
        ]);
    }

    return $tenant;
}

function runTenantAccessibilityMiddleware(?Tenant $tenant, bool $initialized = true): Response
{
    $tenancy = new Tenancy;
    $tenancy->initialized = $initialized;
    $tenancy->tenant = $tenant;
    app()->instance(Tenancy::class, $tenancy);

    if ($tenant?->tenantDatabase !== null) {
        config()->set('database.connections.tenant', $tenant->database()->connection());
    }

    return app(EnsureTenantAccessible::class)->handle(Request::create('/'), fn (Request $request) => response('ok'));
}

function expectTenantAccessibilityThrowsStatusCode(callable $callback, int $statusCode): void
{
    try {
        $callback();
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBe($statusCode);

        return;
    }

    test()->fail("Expected a {$statusCode} HttpException but none was thrown.");
}

it('allows access for active tenants when the database exists', function () {
    $file = 'shop-active-'.Str::uuid().'.sqlite';
    file_put_contents(database_path($file), '');

    $response = runTenantAccessibilityMiddleware(createAccessibleTenant(TenantStatus::ACTIVE, $file));

    @unlink(database_path($file));

    expect($response->getStatusCode())->toBe(200);
});

it('passes through when tenancy is not initialized (central domain)', function () {
    expect(runTenantAccessibilityMiddleware(null, false)->getStatusCode())->toBe(200);
});

it('blocks access for pending tenants with a specific message', function () {
    $response = runTenantAccessibilityMiddleware(createAccessibleTenant(TenantStatus::PENDING));

    expect($response->getStatusCode())->toBe(403)
        ->and($response->getContent())->toContain(__('tenant.unavailable.pending'));
});

it('blocks access for suspended tenants with a specific message', function () {
    $response = runTenantAccessibilityMiddleware(createAccessibleTenant(TenantStatus::SUSPENDED));

    expect($response->getStatusCode())->toBe(403)
        ->and($response->getContent())->toContain(__('tenant.unavailable.suspended'));
});

it('blocks access for expired tenants with a specific message', function () {
    $response = runTenantAccessibilityMiddleware(createAccessibleTenant(TenantStatus::EXPIRED));

    expect($response->getStatusCode())->toBe(403)
        ->and($response->getContent())->toContain(__('tenant.unavailable.expired'));
});

it('blocks access for disabled tenants with a specific message', function () {
    $response = runTenantAccessibilityMiddleware(createAccessibleTenant(TenantStatus::DISABLED));

    expect($response->getStatusCode())->toBe(403)
        ->and($response->getContent())->toContain(__('tenant.unavailable.disabled'));
});

it('returns a 404 when tenancy is initialized without a tenant', function () {
    expectTenantAccessibilityThrowsStatusCode(
        fn () => runTenantAccessibilityMiddleware(null),
        404,
    );
});

it('blocks active tenants whose database does not exist', function () {
    $tenant = createAccessibleTenant(TenantStatus::ACTIVE, 'shop-missing-'.Str::uuid().'.sqlite');

    $response = runTenantAccessibilityMiddleware($tenant);

    expect($response->getStatusCode())->toBe(503)
        ->and($response->getContent())->toContain(__('tenant.unavailable.database'));
});

it('returns a 503 when the tenant database cannot be reached', function () {
    $file = 'shop-down-'.Str::uuid().'.sqlite';
    file_put_contents(database_path($file), '');
    $tenant = createAccessibleTenant(TenantStatus::ACTIVE, $file);

    $connection = Mockery::mock();
    $connection->shouldReceive('getPdo')->andThrow(new PDOException('Connection refused'));
    DB::shouldReceive('connection')->andReturn($connection);

    $response = runTenantAccessibilityMiddleware($tenant);

    @unlink(database_path($file));

    expect($response->getStatusCode())->toBe(503)
        ->and($response->getContent())->toContain(__('tenant.unavailable.database'));
});

it('logs the underlying reason when the tenant database is unreachable', function () {
    Log::spy();
    $tenant = createAccessibleTenant(TenantStatus::ACTIVE, 'shop-missing-'.Str::uuid().'.sqlite');

    runTenantAccessibilityMiddleware($tenant);

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context): bool => $message === 'Tenant database unreachable'
            && $context['tenant_id'] === $tenant->id
            && str_contains($context['exception']->getMessage(), $tenant->database()->getName()));
});

it('surfaces the underlying reason in the local environment', function () {
    app()->detectEnvironment(fn () => 'local');

    $tenant = createAccessibleTenant(TenantStatus::ACTIVE, 'shop-missing-'.Str::uuid().'.sqlite');

    $response = runTenantAccessibilityMiddleware($tenant);

    expect($response->getContent())->toContain($tenant->database()->getName());
});

it('probes the tenant connection directly instead of checking existence over the central connection', function () {
    config()->set('database.connections.mysql', [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'central',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]);

    $tenant = createAccessibleTenant(TenantStatus::ACTIVE, 'shop-mysql-'.Str::uuid().'.sqlite');
    $tenant->tenantDatabase()->update([
        'connection' => 'mysql',
        'database' => 'tenant_'.Str::uuid(),
    ]);
    $tenant->unsetRelation('tenantDatabase');

    $manager = Mockery::mock(MySQLDatabaseManager::class)->makePartial();
    $manager->shouldNotReceive('databaseExists');
    app()->instance(MySQLDatabaseManager::class, $manager);

    $connection = Mockery::mock();
    $connection->shouldReceive('getPdo')->andReturnNull();
    DB::shouldReceive('connection')->andReturn($connection);

    $response = runTenantAccessibilityMiddleware($tenant);

    expect($response->getStatusCode())->toBe(200);
});
