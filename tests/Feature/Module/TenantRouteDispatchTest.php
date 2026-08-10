<?php

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\User;
use App\Module\ModuleBootLoader;
use App\Module\ModuleDiscoveryManager;
use App\Module\TenantRouteCache;
use App\Module\TenantRouteConflictException;
use App\Module\TenantRouteLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Stancl\Tenancy\Events\TenantCreated;

/**
 * 按域名分发模块租户路由的集成测试。
 *
 * 每个用例都会新建 application（Laravel 测试默认行为），因此
 * 上一个用例动态注册的路由不会残留到下一个用例。
 */
function testingModuleBaseDir(): string
{
    return storage_path('framework/testing/tenant-routes-test');
}

function makeTestingModuleDir(string $name): string
{
    $dir = testingModuleBaseDir().'/modules/'.$name;

    (new Filesystem)->ensureDirectoryExists($dir.'/routes');

    return $dir;
}

function writeModuleTenantRoutes(string $dir, string $routes): void
{
    (new Filesystem)->put($dir.'/routes/tenant.php', $routes);
}

function createDispatchModule(string $dir, string $package = 'test/route-module', string $status = 'active'): Module
{
    return Module::create([
        'package_name' => $package,
        'name' => 'Route Module',
        'providers' => [],
        'path' => $dir,
        'areas' => ['tenant'],
        'status' => ModuleStatus::from($status),
        'installed_at' => now(),
    ]);
}

function createDispatchTenant(string $domain, string $status = 'active'): Tenant
{
    Event::fake([TenantCreated::class]);

    $tenant = Tenant::create([
        'id' => 'tenant-'.Str::uuid(),
        'name' => 'Test Shop',
        'user_id' => User::factory()->create()->id,
        'status' => $status,
    ]);

    $databaseName = 'testing/tenant-'.Str::uuid().'.sqlite';
    $databasePath = base_path('database').'/'.$databaseName;
    (new Filesystem)->ensureDirectoryExists(dirname($databasePath));
    (new Filesystem)->put($databasePath, '');

    $tenant->setDatabaseConnection([
        'connection' => 'sqlite',
        'database' => $databaseName,
    ]);

    $tenant->domains()->create(['domain' => $domain]);

    return $tenant;
}

function moduleHelloRoutes(): string
{
    return <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/hello', fn () => response('module-hello'));
PHP;
}

beforeEach(function () {
    $dir = testingModuleBaseDir();

    (new Filesystem)->deleteDirectory($dir);
    (new Filesystem)->ensureDirectoryExists($dir);

    TenantRouteCache::flushAll();
});

afterEach(function () {
    (new Filesystem)->deleteDirectory(testingModuleBaseDir());
    (new Filesystem)->deleteDirectory(base_path('database/testing'));
    TenantRouteCache::flushAll();
});

it('dispatches enabled tenant module routes for the tenant domain', function () {
    $dir = makeTestingModuleDir('dispatch-enabled');
    writeModuleTenantRoutes($dir, moduleHelloRoutes());

    $module = createDispatchModule($dir);
    $tenant = createDispatchTenant('dispatch-enabled.test');
    $tenant->setModuleEnabled($module->id, true);

    app(ModuleDiscoveryManager::class)->flushCache();

    $this->get('http://dispatch-enabled.test/hello')
        ->assertOk()
        ->assertSee('module-hello');
});

it('resolves named routes registered by tenant module route files', function () {
    $dir = makeTestingModuleDir('dispatch-named');
    writeModuleTenantRoutes($dir, <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/named', fn () => response('module-named'))->name('module.named.index');
PHP);

    $module = createDispatchModule($dir);
    $tenant = createDispatchTenant('dispatch-named.test');
    $tenant->setModuleEnabled($module->id, true);

    app(ModuleDiscoveryManager::class)->flushCache();

    $this->get('http://dispatch-named.test/named')
        ->assertOk()
        ->assertSee('module-named');

    expect(app('router')->has('module.named.index'))->toBeTrue()
        ->and(route('module.named.index'))->toBe('http://dispatch-named.test/named');
});

it('returns 404 for module routes not enabled for the tenant', function () {
    $dir = makeTestingModuleDir('dispatch-disabled');
    writeModuleTenantRoutes($dir, moduleHelloRoutes());

    createDispatchModule($dir);
    createDispatchTenant('dispatch-disabled.test');

    app(ModuleDiscoveryManager::class)->flushCache();

    $this->get('http://dispatch-disabled.test/hello')->assertNotFound();
});

it('does not load tenant module routes on central domains', function () {
    $dir = makeTestingModuleDir('dispatch-central');
    writeModuleTenantRoutes($dir, moduleHelloRoutes());

    $module = createDispatchModule($dir);
    $tenant = createDispatchTenant('dispatch-central.test');
    $tenant->setModuleEnabled($module->id, true);

    app(ModuleDiscoveryManager::class)->flushCache();

    $this->get('http://localhost/hello')->assertNotFound();
});

it('does not load module routes when the module is globally inactive', function () {
    $dir = makeTestingModuleDir('dispatch-inactive');
    writeModuleTenantRoutes($dir, moduleHelloRoutes());

    $module = createDispatchModule($dir, status: 'inactive');
    $tenant = createDispatchTenant('dispatch-inactive.test');
    $tenant->setModuleEnabled($module->id, true);

    app(ModuleDiscoveryManager::class)->flushCache();

    $this->get('http://dispatch-inactive.test/hello')->assertNotFound();
});

it('returns 404 for unknown tenant domains', function () {
    $this->get('http://unknown-tenant.test/hello')->assertNotFound();
});

it('throws TenantRouteConflictException when enabled modules register the same route', function () {
    $dirA = makeTestingModuleDir('conflict-a');
    writeModuleTenantRoutes($dirA, <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/same', fn () => 'a');
PHP);

    $dirB = makeTestingModuleDir('conflict-b');
    writeModuleTenantRoutes($dirB, <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/same', fn () => 'b');
PHP);

    $loader = app(TenantRouteLoader::class);

    expect(fn () => $loader->registerRouteFiles([
        $dirA.'/routes/tenant.php',
        $dirB.'/routes/tenant.php',
    ]))->toThrow(TenantRouteConflictException::class);
});

it('does not consider same-URI routes across middleware groups a conflict', function () {
    $dir = makeTestingModuleDir('no-conflict');
    writeModuleTenantRoutes($dir, <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/shared', fn () => 'module-route');
PHP);

    $module = createDispatchModule($dir);
    $tenant = createDispatchTenant('no-conflict.test');
    $tenant->setModuleEnabled($module->id, true);

    app(ModuleDiscoveryManager::class)->flushCache();

    $this->get('http://no-conflict.test/shared')
        ->assertOk()
        ->assertSee('module-route');
});

it('maps tenants to stable route cache keys', function () {
    $tenant = new Tenant(['id' => 'acme']);

    expect(TenantRouteCache::tenantKey($tenant))->toBe('tenant_'.sha1('acme'))
        ->and(TenantRouteCache::fileFor(TenantRouteCache::tenantKey($tenant)))
        ->toBe(base_path('bootstrap/cache/tenant-routes/tenant_'.sha1('acme').'.php'))
        ->and(TenantRouteCache::fileFor('central'))
        ->toBe(base_path('bootstrap/cache/tenant-routes/central.php'));
});

it('flushes the tenant route cache when a module is disabled for the tenant', function () {
    $dir = makeTestingModuleDir('cache-flush');
    $module = createDispatchModule($dir);
    $tenant = createDispatchTenant('cache-flush.test');
    $tenant->setModuleEnabled($module->id, true);

    $cacheFile = TenantRouteCache::fileFor(TenantRouteCache::tenantKey($tenant));
    (new Filesystem)->ensureDirectoryExists(dirname($cacheFile));
    (new Filesystem)->put($cacheFile, '<?php // cached');

    app(ModuleBootLoader::class)->disableForTenant($module, $tenant);

    expect((new Filesystem)->exists($cacheFile))->toBeFalse();
});

it('flushes all tenant route caches when a module is disabled globally', function () {
    $dir = makeTestingModuleDir('cache-flush-all');
    $module = createDispatchModule($dir);

    $tenantA = createDispatchTenant('flush-all-a.test');
    $tenantA->setModuleEnabled($module->id, true);
    $tenantB = createDispatchTenant('flush-all-b.test');
    $tenantB->setModuleEnabled($module->id, true);

    $cacheFileA = TenantRouteCache::fileFor(TenantRouteCache::tenantKey($tenantA));
    $cacheFileB = TenantRouteCache::fileFor(TenantRouteCache::tenantKey($tenantB));
    (new Filesystem)->ensureDirectoryExists(dirname($cacheFileA));
    (new Filesystem)->put($cacheFileA, '<?php // cached');
    (new Filesystem)->put($cacheFileB, '<?php // cached');

    app(ModuleBootLoader::class)->disable($module);

    expect((new Filesystem)->exists($cacheFileA))->toBeFalse()
        ->and((new Filesystem)->exists($cacheFileB))->toBeFalse();
});

it('lists the module routes of the given tenant via the routes-list command', function () {
    $dir = makeTestingModuleDir('routes-list');
    writeModuleTenantRoutes($dir, moduleHelloRoutes());

    $module = createDispatchModule($dir);
    $tenant = createDispatchTenant('routes-list.test');
    $tenant->setModuleEnabled($module->id, true);

    app(ModuleDiscoveryManager::class)->flushCache();

    Artisan::call('tenancy:routes-list', ['tenant' => $tenant->getTenantKey()]);

    expect(Artisan::output())
        ->toContain('GET|HEAD')
        ->toContain('hello')
        ->toContain($tenant->getTenantKey());
});

it('warns when the given tenant has no enabled module routes', function () {
    $tenant = createDispatchTenant('routes-empty.test');

    $this->artisan('tenancy:routes-list', ['tenant' => $tenant->getTenantKey()])
        ->assertSuccessful()
        ->expectsOutputToContain('该租户未启用任何模块路由');
});

it('fails for an unknown tenant id in the routes-list command', function () {
    $this->artisan('tenancy:routes-list', ['tenant' => 'does-not-exist'])
        ->assertFailed()
        ->expectsOutputToContain('租户不存在');
});
