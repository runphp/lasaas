# Changelog

本项目遵循 [Keep a Changelog](https://keepachangelog.com/zh-CN/1.1.0/) 约定，
版本号遵循 [Semantic Versioning](https://semver.org/lang/zh-CN/)。

## [Unreleased]

### 新增

- 域名 → 租户路由分发中间件 `InitializeTenantAndDispatchRoutes`
  - 中央域名加载中央路由缓存，租户域名解析租户后按需加载模块路由
  - 每个请求只有一种路由集合，会话落租户连接（位于 StartSession 之前）
- 按域名分发的租户路由缓存体系 `TenantRouteCache`
  - 缓存目录 `bootstrap/cache/tenant-routes/`：1 份中央缓存 + N 份租户缓存
  - 新增命令 `tenancy:routes-cache`（预编译中央与全部租户路由）
  - 新增命令 `tenancy:routes-clear`（清除按域名分发的路由缓存）
  - 新增命令 `tenancy:routes-list {tenant}`（列出指定租户启用的模块路由）
- 租户路由加载器 `TenantRouteLoader`
  - 仅加载当前租户启用模块的 `routes/tenant.php`，跨租户冲突天然消除
  - 同租户内多模块路由冲突在注册时显式抛出 `TenantRouteConflictException`
- 中央路由统一入口
  - `RouteServiceProvider` + `CentralRouteManager` + `CentralRouteQueue`
  - 根 `routes/web.php` 与各 central 模块路由统一在中央域名分组内注册，杜绝跨域名越权
- 租户可用性校验服务 `TenantAvailability`，供 `EnsureTenantAccessible` 中间件复用
- 新增 `TenantRouteDispatchTest`，覆盖租户路由分发与冲突检测

### 重构

- 将单体 `ModuleManager` 拆分为四个职责单一的管理器：
  - `ModuleDiscoveryManager`：模块元数据发现/排序/面板插件发现
  - `ModuleBootLoader`：上下文模块加载与生命周期（含租户路由缓存失效）
  - `ModuleSettingManager`：Spatie Settings 设置逻辑
  - `CentralRouteManager`：中央路由收集与分发
- `EnsureTenantAccessible` 复用 `TenantAvailability`，消除重复的租户状态/数据库校验逻辑
- 根 `routes/web.php` 移除 `Route::domain()` 循环包裹，由中央路由统一入口接管
- 模块启用/禁用/卸载与租户级安装/卸载后自动失效对应租户路由缓存

### 变更

- `bootstrap/app.php`：注册 `RouteServiceProvider`，全局中间件首位挂载分发中间件
- `composer.json`：新增 `lasaas/address` 模块依赖，调整 module installer-paths 支持 vendor 分组
- `tests/Pest.php`：测试作用域扩展至 `packages`，覆盖模块包测试
- 新增租户库 `settings` 表迁移（`database/migrations/tenant/2022_12_14_083707_create_settings_table.php`）

### 修复

- 模块生命周期操作（启用/禁用/卸载）后路由缓存及时失效，避免过期路由残留
