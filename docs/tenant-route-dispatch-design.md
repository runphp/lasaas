# 租户路由按域名分发 + 按域名缓存 技术设计文档

> 状态：评审中（未开始编码）
> 涉及范围：`app/Module/`、`app/Http/Middleware/`、`app/Providers/`、`app/Console/Commands/`、`bootstrap/app.php`、`routes/tenant.php`、`config/tenancy.php`、模块 `routes/web.php`/`routes/tenant.php`

---

## 一、背景与目标

当前系统所有租户模块的 `routes/tenant.php` 在应用 boot 阶段被一次性全部注册进全局路由表（`ModuleBootLoader::registerTenantModuleRoutes()`）。带来三个问题：

1. **路由冲突**：不同租户启用模块不同，可能定义相同路径；全局预注册先注册先匹配，必然互相遮蔽，且未启用模块路由也可能被命中——**跨租户、跨模块串路由**。
2. **越权面**：禁用模块路由仍存在于路由表，需额外守卫兜底。
3. **膨胀与不可缓存**：每个请求携带全部模块租户路由，无法按租户缓存。

### 目标架构（两条铁律）

1. **域名决定唯一一份路由集合**：域名只有两类——中央域名或某个租户域名。域名确定后，请求只需要一份路由表：中央路由集 或（该租户路由集）。因此缓存结构为 **1 份中央应用缓存 + N 份租户应用缓存**；缓存键**按租户**（一个租户可绑多域名，域名解析后共享同一份缓存），文件名 `central.php` + `tenant_{sha1(tenantKey)}.php`。
2. **Session 在确定上下文后才启动**：session 是分租户隔离的（中央库 vs 租户库 session 表）。必须在判定"中央应用 / 具体租户"并完成租户初始化**之后**才能启动 session，使 session 直接落在正确连接上。

---

## 二、现状梳理

### 2.1 当前请求生命周期（已从框架源码确认）

```
sendRequestThroughRouter()
  → 全局中间件 pipeline（bootstrap/app.php 全局栈）
  → Router::dispatch() → findRoute()          ← 路由匹配
  → runRouteWithinStack()                      ← 路由中间件（web 组 StartSession、tenancy 组）才执行
```

**关键结论**：路由中间件（含 `InitializeTenancyByDomain`、`web` 组的 `StartSession`）在 `findRoute()` 之后执行。想在路由匹配前决定加载哪份路由，必须放在**全局中间件**；且全局中间件天然早于 `StartSession`——**铁律 2 由架构时序保证**（见 §6.4）。

### 2.2 相关文件现状

| 文件 | 现状 |
| --- | --- |
| `app/Module/ModuleBootLoader.php` | `loadCentralModules()` 末尾调 `registerTenantModuleRoutes()`，boot 阶段全量注册模块租户路由（无域名包裹，带 tenancy 中间件栈） |
| `app/Module/CentralRouteManager.php` | `dispatchAll()` 用 `Route::domain(central_domains)->middleware('web')` 包裹注册中央路由 |
| `app/Providers/RouteServiceProvider.php` | `map()` → `CentralRouteManager::dispatchAll()` |
| `app/Providers/TenancyServiceProvider.php` | `mapRoutes()` 在 `app->booted()` 回调注册框架 `routes/tenant.php`；`configureLivewireRoute()` 注册 `POST /livewire/update` |
| `routes/tenant.php` | 自带中间件组 `[InitializeTenancyByDomain, EnsureTenantAccessible, 'web', PreventAccessFromCentralDomains]` |
| `app/Http/Middleware/EnsureTenantAccessible.php` | 路由中间件：租户状态 + DB 可达性校验 |
| 模块 `routes/web.php` | 裸业务路由，被 CentralRouteManager 域名包裹注册 |
| 模块 `routes/tenant.php` | 裸业务路由（如 address 带 `auth/verified/localization.*`） |

### 2.3 已验证事实

- **`route:cache` 可跑通**：已在容器内实测 `ddev artisan route:cache` 成功——当前全部路由（Livewire SFC `Route::livewire`、`Route::view`、模块租户路由、控制器路由）均可序列化。按域名缓存路由集合在机制上完全可行。
- **`RouteCollection` 可序列化**：Laravel 的 `route:cache` 就是 `compile()` + `var_export` 到 stub 文件，请求时 `require` 返回集合 → `Router::setRoutes()`。本方案复用同一机制。
- `DomainTenantResolver::resolve($host)`：未找到租户抛 `TenantCouldNotBeIdentifiedOnDomainException`。
- `tenancy()->initialize()` 幂等（同租户早退），触发 `TenancyInitialized` → `loadTenantModules()`。
- `Tenant::getEnabledModules()`：返回启用且中央 active 的模块包名列表。
- `ModuleDiscoveryManager::discover()` / `supportsArea($module,'tenant')` / `resolveModulePath()`：模块发现与解析。

---

## 三、目标时序

```
请求进入
  → 全局中间件 InitializeTenantAndDispatchRoutes（必须注册在全局栈第一位）
       ├─ host ∈ central_domains
       │    → 上下文 = 中央；session 将由 web 组按中央库启动
       │    → 有缓存：Router::setRoutes(require cache/central.php)
       │    → 无缓存：中央路由已在 boot 注册，直接放行
       │    → next
       └─ host ∉ central_domains（租户域名）
            → DomainTenantResolver::resolve(host)          // 未知域名 → 404
            → tenancy()->initialize(tenant)                // 此时默认连接已切到租户库
            → EnsureTenantAccessible 校验（状态 + DB 可达性）  // 403 / 503
            → 有缓存：Router::setRoutes(require cache/tenant_{sha1(tenantKey)}.php)
            → 无缓存：TenantRouteLoader 动态注册该租户启用模块的 routes/tenant.php
            → next                                        // 之后 findRoute() 与 StartSession 才执行
```

- **缓存优先**（生产）：`setRoutes()` 整表替换为域名专属缓存集合。
- **动态兜底**（开发/缓存缺失）：当前租户动态注册模块路由，并做同租户路由冲突检测。
- 中央请求全程不初始化租户、不加载任何租户路由；租户请求不加载任何中央业务路由（中央路由带 `Route::domain(central_domains)` 约束，租户域名下不可达，双向隔离天然成立）。

---

## 四、文件改动清单

### 4.1 新增

| 文件 | 职责 |
| --- | --- |
| `app/Http/Middleware/InitializeTenantAndDispatchRoutes.php` | 全局中间件（**注册到全局栈第一位**）：域名判定 → 租户初始化 → 可用性校验 → 加载域名专属路由（缓存优先，动态兜底） |
| `app/Module/TenantRouteLoader.php` | 租户路由加载器：解析当前租户启用模块路由文件、执行注册、同租户冲突检测；供分发中间件与缓存命令复用 |
| `app/Module/TenantRouteConflictException.php` | 同租户内路由冲突异常 |
| `app/Console/Commands/TenancyCacheRoutesCommand.php` | `tenancy:routes-cache`：为中央 + 每个租户各生成一份缓存路由集合到 `bootstrap/cache/tenant-routes/` |
| `app/Console/Commands/TenancyClearRoutesCommand.php` | `tenancy:routes-clear`：清空 `bootstrap/cache/tenant-routes/` |

### 4.2 修改

| 文件 | 改动 |
| --- | --- |
| `bootstrap/app.php` | 全局栈 `prepend(InitializeTenantAndDispatchRoutes::class)`（第一位） |
| `app/Module/ModuleBootLoader.php` | 删除 `registerTenantModuleRoutes()` 及 `loadCentralModules()` 中的调用；新增 `loadCurrentTenantRoutes(Tenant)` 委托 `TenantRouteLoader`；生命周期方法（enable/disable/uninstall/enableForTenant/disableForTenant/uninstallForTenant）失效对应租户的缓存文件 |
| `routes/tenant.php` | 去掉 `InitializeTenancyByDomain`/`EnsureTenantAccessible`/`PreventAccessFromCentralDomains`，保留 `web` 与业务中间件（`localization.*`/`auth`/`verified`/`EnsureTeamMembership`）。该文件在上下文确定后加载（boot 或缓存构建时），域名正确性由分发层保证 |
| `app/Http/Middleware/EnsureTenantAccessible.php` | 抽取出独立校验服务 `TenantAvailability`（状态 + DB 可达性），中间件与分发逻辑共用 |
| `app/Providers/TenancyServiceProvider.php` | `mapRoutes()` 保持 booted 回调注册框架 `routes/tenant.php`；`configureLivewireRoute()`/文件上传配置保持（tenancy 幂等早退）；**可选**：`route:cache` 生成时跳过模块租户路由（删除 boot 注册后自然满足） |
| `app/Module/CentralRouteManager.php` | **不改** |

### 4.3 不改

- 模块 `routes/web.php`：继续被 CentralRouteManager 域名包裹。
- 模块 `routes/tenant.php`：内容不变（仅业务中间件）。
- `config/tenancy.php`：不改（分发中间件注册在 bootstrap/app.php）。

---

## 五、核心代码方案

### 5.1 分发中间件

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Module\TenantRouteLoader;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InitializeTenantAndDispatchRoutes
{
    public function __construct(
        protected Tenancy $tenancy,
        protected DomainTenantResolver $resolver,
        protected TenantRouteLoader $loader,
        protected Router $router,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if (in_array($host, config('tenancy.central_domains'), true)) {
            $this->loadCachedRoutes('central');

            return $next($request);
        }

        try {
            $tenant = $this->resolver->resolve($host);
        } catch (\Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException) {
            throw new NotFoundHttpException;
        }

        $this->tenancy->initialize($tenant);

        $response = app(TenantAvailability::class)->check($request);

        if ($response instanceof Response) {
            return $response; // 403 / 503
        }

        if (! $this->loadCachedRoutes('tenant_'.sha1($tenant->getTenantKey()))) {
            $this->loader->load($tenant); // 开发兜底：动态注册 + 冲突检测
        }

        return $next($request);
    }

    protected function loadCachedRoutes(string $key): bool
    {
        $file = bootstrap_path('cache/tenant-routes/'.$key.'.php');

        if (! file_exists($file)) {
            return false;
        }

        $this->router->setRoutes(require $file);

        return true;
    }
}
```

> `setRoutes(RouteCollection)` 是 Router 既有 API（`route:cache` 加载路径）。`require $file` 返回已 `compile()` 的完整 RouteCollection，整表替换。

### 5.2 租户路由加载器

```php
<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Tenant;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

class TenantRouteLoader
{
    /** @var array<string,true> 本次加载已注册的 method+uri 签名 */
    protected array $registered = [];

    public function __construct(protected ModuleDiscoveryManager $discovery) {}

    public function load(Tenant $tenant): void
    {
        $this->registered = [];

        $enabledPackages = $tenant->getEnabledModules();

        foreach ($this->discovery->discover() as $module) {
            if (! $this->discovery->supportsArea($module, 'tenant')) {
                continue;
            }

            if (! in_array($module->package_name, $enabledPackages, true)) {
                continue;
            }

            $path = $this->discovery->resolveModulePath($module->package_name).'/routes/tenant.php';

            if (! file_exists($path)) {
                continue;
            }

            RouteFacade::middleware('web')->group($path);
        }

        $this->assertNoConflicts();
    }

    protected function assertNoConflicts(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            if (! in_array($route->getAction('uses'), $this->trackedActions(), true)) {
                continue;
            }

            $signature = $route->methods()[0].' '.$route->uri();

            if (isset($this->registered[$signature])) {
                throw new TenantRouteConflictException(
                    "同租户路由冲突: {$signature}（{$this->registered[$signature]} 与 {$route->getActionName()}）"
                );
            }

            $this->registered[$signature] = $route->getActionName();
        }
    }
}
```

> **冲突检测范围**：只统计本次加载新增的路由（不扫全表），避免把"中央路由（带域名约束）与租户路由同路径"误判——二者可共存，宿主约束不同。
> **无域名约束**：模块 `routes/tenant.php` 不带 `Route::domain()`，因为只在对应租户请求时加载/缓存，域名正确性由分发层保证。

### 5.3 按域名缓存命令（核心新增）

```php
// app/Console/Commands/TenancyCacheRoutesCommand.php
class TenancyCacheRoutesCommand extends Command
{
    protected $signature = 'tenancy:routes-cache';

    public function handle(): int
    {
        $dir = base_path('bootstrap/cache/tenant-routes');
        File::ensureDirectoryExists($dir);

        $this->buildContext('central', null);                       // 中央路由集
        $this->newLine();

        tenancy()->query()->get()->each(function (Tenant $tenant) {
            if ($tenant->domains()->count() === 0) {
                return; // 无域名租户不可达，不生成缓存
            }

            $key = 'tenant_'.sha1($tenant->getTenantKey());
            $this->buildContext($key, $tenant);
        });

        return self::SUCCESS;
    }

    protected function buildContext(string $key, ?Tenant $tenant): void
    {
        // 1. 全新应用容器（复用 route:cache 的 getFreshApplication() 机制）
        $app = require $this->laravel->bootstrapPath('app.php');

        // 2. 租户上下文：在 boot 完成、路由已注册基础集后，初始化租户并加载其模块路由
        if ($tenant) {
            $app->booted(function () use ($app, $tenant) {
                $app->make(Tenancy::class)->initialize($tenant);
                $app->make(TenantRouteLoader::class)->load($tenant);
            });
        }

        // 3. 完整 boot：注册中央路由（CentralRouteManager）+ 框架 routes/tenant.php
        $app->make(Kernel::class)->bootstrap();

        // 4. 复用 route:cache 的序列化方式：compile() + var_export 到 stub 文件
        $routes = $app['router']->getRoutes();
        $routes->refreshNameLookups();
        $routes->refreshActionLookups();
        $routes->compile();

        $stub = File::get($app->basePath('vendor/laravel/framework/src/Illuminate/Foundation/Console/stubs/routes.stub'));
        File::put(base_path("bootstrap/cache/tenant-routes/{$key}.php"),
            str_replace('{{routes}}', var_export($routes, true), $stub));

        $this->info("已缓存: {$key}（{$routes->count()} 条路由）");
    }
}
```

要点：
- **每上下文全新容器**：避免多个租户路由在同一容器内互相残留，与 `route:cache` 的 `getFreshApplication()` 同机制。
- **命名（1 + N）**：`central.php` + 每租户 `tenant_{sha1(tenantKey)}.php`，与分发中间件 `loadCachedRoutes()` 的键一致，无需 manifest。
- **多域名共享**：一个租户绑多个域名时由 `DomainTenantResolver` 全部解析到同一租户 → 命中同一份缓存文件。
- **central 上下文**：boot 后集合 = 中央路由 + 框架租户路由（无模块租户路由，因 boot 注册已删除）——与今天 `route:cache` 产物等价。
- **租户上下文**：集合 = 中央路由（带域名约束，租户域下不可达，无害）+ 框架租户路由 + 该租户启用模块的租户路由。
- **编译时错误**：同租户路由冲突在缓存构建时即抛 `TenantRouteConflictException`，构建失败即暴露，不会上线后才炸。
- **DB 依赖**：需在 DB 可达环境执行（`ddev artisan tenancy:routes-cache`）。

### 5.4 缓存失效

- `tenancy:routes-clear` 清空 `bootstrap/cache/tenant-routes/`。
- 模块生命周期操作（全局 enable/disable/uninstall、租户 enableForTenant/disableForTenant/uninstallForTenant）后需重新生成缓存；`ModuleBootLoader` 生命周期方法内对受影响租户调用 `TenancyClearRoutesCommand` 的清理逻辑，或部署脚本在模块变更后统一重跑 `tenancy:routes-cache`。
- 路由文件变更：部署时重跑命令即可（命令幂等，全量重建）。

---

## 六、关键细节与边界

### 6.1 内置 `route:cache` 与本方案的关系

- 删除 boot 阶段 `registerTenantModuleRoutes()` 后，内置 `route:cache` 缓存的是"中央路由 + 框架租户路由"（基础集）。
- **生产建议只使用 `tenancy:routes-cache`**：它覆盖中央与全部租户，含域名唯一语义。内置 `route:cache` 可选（作为基础集预热），分发中间件始终以 `tenant-routes/{key}.php` 为准，两者不冲突。
- 明确约定：**不要依赖内置 `route:cache` 承担租户路由**。

### 6.2 框架 `routes/tenant.php` 保持 boot 注册

- `TenancyServiceProvider::mapRoutes()` 的 booted 回调继续注册框架租户路由（所有租户共享的公共底座，路径固定无冲突，参与基础集/缓存）。
- 仅去掉文件内的 tenancy 中间件包裹（§4.2）。域内正确性由分发层保证（中央域下由 `PreventAccessFromCentralDomains` 拦截）。

### 6.3 Livewire 兼容性

- Livewire SFC 页面路由：GET 路由来自域名专属集合（缓存或动态），可正常匹配。
- `POST /livewire/update`：boot 注册，带 `[InitializeTenancyByDomain, EnsureTenantAccessible]`；分发中间件已初始化同租户，幂等早退，无副作用。
- 文件上传预览：同理。
- 已实测：`route:cache` 成功说明这些路由均可序列化，缓存构建无阻塞。

### 6.4 Session 时序（铁律 2，架构保证）

- **要求**：session 必须在确定上下文（中央 / 具体租户）之后启动——session 按上下文分库（中央库 vs 租户库 session 表）。
- **保证链**：
  1. `InitializeTenantAndDispatchRoutes` **prepend 到全局栈第一位**，是所有全局中间件最先执行的；
  2. `StartSession` 属于 `web` **路由**中间件组，在 `findRoute()` 之后才执行；
  3. 因此 session 启动前，租户已初始化、默认连接已切租户库；
  4. `DatabaseSessionHandler` 连接**懒解析**：`StartSession` 首次 `session()->start()` 时取 `app('db')->connection(config('session.connection') ?? null)` → 默认连接 → 已是租户连接。handler 直接绑定租户连接，**无需反射切换**。
- 现有 `SessionTenancyBootstrapper` 的反射切换是旧时序（租户初始化晚于会话启动）的补救，新时序下 no-op 无害，保留作 `revert()` 兜底。
- **硬约束**：不得在任何全局中间件、且不得在本分发中间件**之前**访问 `session()`。架构测试固化：断言分发中间件位于全局中间件列表首位。

### 6.5 `route()` 命名路由

- 租户请求期间路由表 = 中央（域名约束）+ 框架租户 + 当前租户模块路由。`route('tenant.xxx')` 可用。
- 跨域链接（中央后台 ↔ 租户后台）必须用完整 URL，不能依赖 `route()`。约定文档化。

### 6.6 每请求成本

- 生产（缓存命中）：`setRoutes(require ...)` = 一次文件 require + 对象还原，无路由注册计算，接近内置 `route:cache` 的性能。
- 开发（无缓存）：每请求动态注册当前租户模块路由，成本可忽略。

---

## 七、测试计划

### 7.1 新增（`tests/Feature/Module/TenantRouteDispatchTest.php`）

| # | 用例 | 断言 |
| --- | --- | --- |
| 1 | 租户域名访问已启用模块路由（动态模式） | 200 |
| 2 | 租户域名访问未启用模块路由（动态模式） | 404（未注册，非 500） |
| 3 | 两租户启用不同模块，各自路由互不串、访问对方模块路由 404 | 各自 200 / 404 |
| 4 | 中央域名不初始化租户、不加载租户路由 | 中央路由可用，租户路由 404 |
| 5 | 未知域名 | 404 |
| 6 | 租户非 ACTIVE / DB 不可达 | 403 / 503 |
| 7 | 模块启停后路由即时生效（动态模式 + 缓存模式） | disable 后 404，enable 后 200 |
| 8 | 同租户两模块定义相同 method+uri | 抛 `TenantRouteConflictException` |
| 9 | Livewire SFC 页面在租户域名可用 | 页面 200、可渲染 |
| 10 | 登录后 session 落租户库 | session 表写入租户连接 |
| 11 | 分发中间件位于全局栈首位 | 架构断言 |
| 12 | `tenancy:routes-cache` 生成后各域名缓存集合正确 | central 无模块租户路由；tenant 含该租户模块路由 |
| 13 | 缓存模式请求行为与动态模式一致 | 访问结果一致 |

### 7.2 回归

- `packages/custom/lasaas/address/tests/Feature/AddressPageTest.php`
- `tests/Feature/Module/*`、`tests/Feature/Tenant/*`
- `tests/Feature/DashboardTest.php`、`tests/Feature/Auth/*`
- `tests/Feature/Filament/LanguageSwitchTest.php`

### 7.3 手动验证（全部经 `ddev`）

1. `ddev artisan tenancy:routes-cache` → `ddev artisan tenancy:routes-clear` 往返正常。
2. 登录 A 租户域名访问其启用模块路由 200；访问 B 特有模块路由 404。
3. 中央后台访问租户域名路由 404。
4. 缓存构建时注入同租户冲突模块 → 命令报 `TenantRouteConflictException`。
5. `ddev artisan route:list`：boot 后无模块租户路由。

---

## 八、风险与对策

| 风险 | 等级 | 对策 |
| --- | --- | --- |
| 有全局中间件在分发中间件前碰 session | 高 | 架构铁律 + 测试 #11；prepend 首位 |
| 缓存与模块启停不一致 | 中 | 生命周期方法失效缓存 + 部署重跑 `tenancy:routes-cache` + 测试 #7 |
| 缓存构建依赖 DB（模块表/租户表/租户库） | 中 | 命令在 DB 可达环境执行；失败显式报错不静默 |
| Livewire `/livewire/update` 在分发后再次初始化租户 | 中 | 幂等早退验证；跨租户场景测试 |
| 同租户冲突误判（中央 vs 租户同路径） | 低 | 冲突检测仅限本次加载路由（§5.2） |
| 跨域 `route()` 失败 | 低 | 约定：跨域用完整 URL |
| 内置 `route:cache` 被误用承担租户路由 | 低 | 文档化：租户路由只由 `tenancy:routes-cache` 承担 |

---

## 九、实施步骤（编码顺序）

1. 新增 `TenantRouteConflictException`、`TenantRouteLoader`（动态注册 + 限定范围冲突检测）。
2. 修改 `ModuleBootLoader`：删除 `registerTenantModuleRoutes()`；新增 `loadCurrentTenantRoutes()`；生命周期方法加缓存失效钩子。
3. 新增 `TenancyCacheRoutesCommand` / `TenancyClearRoutesCommand`。
4. 新增分发中间件 `InitializeTenantAndDispatchRoutes`，`bootstrap/app.php` prepend 首位。
5. 修改 `routes/tenant.php` 去掉 tenancy 中间件包裹。
6. 抽出 `TenantAvailability` 校验服务，`EnsureTenantAccessible` 与分发中间件共用。
7. 补测试（§7.1），跑回归（§7.2）。
8. `ddev exec vendor/bin/pint --format agent` 格式化；`ddev artisan tenancy:routes-cache` + `route:list` 手动验证（§7.3）。

---

## 十、验收标准

1. 域名决定唯一一份路由集合：中央域名加载中央集，租户域名加载该租户集，互不渗透。
2. 未启用模块路由 404（物理不存在）；跨租户同路径互不冲突。
3. 生产走 `tenancy:routes-cache` 缓存；开发走动态注册，行为一致。
4. session 在上下文确定后启动，直接落正确连接（中央库/租户库）。
5. 同租户内多模块路由冲突在构建期即显式报错。
6. `route:list` boot 后无模块租户路由；内置 `route:cache` 不承担租户路由。
