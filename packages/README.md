# Lasaas 模块系统

Lasaas 采用 Drupal 式模块系统：功能以 `lasaas-module` 类型的 Composer 包形式存在，由中央应用统一管控，并可选择性安装到租户应用。

- **存放位置**：`packages/contrib/{vendor}/{name}/`（三方模块）与 `packages/custom/{vendor}/{name}/`（自研模块）
- **识别依据**：`composer.json` 中 `"type": "lasaas-module"`
- **同步方式**：`php artisan module:sync` 将磁盘上的模块包同步到 `modules` 表（参考 Drupal：模块的存亡以文件系统为准）

---

## 1. 模块目录结构

```
packages/custom/{vendor}/{name}/
├── composer.json                  # 必须声明 type: "lasaas-module"
├── src/                           # PSR-4 根命名空间（autoload 缓存据此生成）
│   └── {Vendor}\{Module}\
│       ├── BlogServiceProvider.php    # 继承 App\Module\ModuleServiceProvider
│       └── Filament/
│           └── Plugins/               # 后台面板插件，按约定自动发现
│               ├── AdminBlogPlugin.php    # 中央 admin 面板
│               └── TenantBlogPlugin.php   # 租户 admin 面板
├── routes/
│   ├── web.php                    # 中央应用路由（仅 central 区域模块加载，与框架 routes/web.php 对应）
│   └── tenant.php                 # 租户路由，自动挂载租户中间件组
├── database/
│   └── migrations/                # 中央迁移（install 时运行，uninstall 时回滚）
│       └── tenant/                # 租户迁移（tenantInstall 时运行）
├── config/*.php                   # 模块默认配置
└── resources/views/               # 视图，命名空间为包名（/ 转 -）
```

## 2. composer.json

```json
{
    "name": "lasaas/blog",
    "type": "lasaas-module",
    "autoload": {
        "psr-4": {
            "Lasaas\\Blog\\": "src/"
        }
    },
    "extra": {
        "lasaas-module": {
            "name": "博客",
            "description": "博客模块",
            "areas": ["central", "tenant"],
            "weight": 0,
            "after": ["lasaas/seo"],
            "providers": ["Lasaas\\Blog\\BlogServiceProvider"]
        }
    }
}
```

| 字段 | 说明 | 默认 |
| --- | --- | --- |
| `type` | 必须为 `lasaas-module`，否则不识别 | — |
| `autoload.psr-4` | 提供模块代码映射，`module:sync` 据此生成 autoload 缓存 | 必填 |
| `areas` | `["central"]` 仅中央、`["tenant"]` 仅租户、二者皆可 | `["tenant"]` |
| `weight` | 加载权重，越小越先 | `0` |
| `after` | 非强依赖但须在其后加载的模块包名列表 | `[]` |
| `providers` | ServiceProvider 类名，缺省时自动扫描 `*ServiceProvider` | 自动 |

依赖关系自动分析：`require` 中同为 `lasaas-module` 的包会被识别为模块依赖。

## 3. 生命周期

```
磁盘同步 → inactive → enable（首次：install）→ active
                     → disable → inactive
                     → uninstall（回滚中央迁移 + 清理）→ 删除记录

租户侧（中央管理员按租户安装）：
module:tenant-enable（首次：tenantInstall）→ tenant_modules.enabled = true → tenantOnEnable
→ tenant-disable → tenantOnDisable → tenant-uninstall（回滚租户迁移）→ 删除记录
```

### 基类 `App\Module\ModuleServiceProvider` 钩子

模块的 ServiceProvider 继承 `App\Module\ModuleServiceProvider`，可选覆写：

| 钩子 | 上下文 | 触发时机 | 默认行为 |
| --- | --- | --- | --- |
| `install()` | 中央 | 首次 enable | 运行 `database/migrations/` |
| `uninstall()` | 中央 | 卸载 | 回滚 `database/migrations/` |
| `onEnable()` | 中央 | 每次启用 | 无 |
| `onDisable()` | 中央 | 每次禁用 | 无 |
| `tenantInstall(Tenant $tenant)` | 租户 | 首次安装到租户 | 运行 `database/migrations/tenant/` |
| `tenantUninstall(Tenant $tenant)` | 租户 | 从租户卸载 | 回滚 `database/migrations/tenant/` |
| `tenantOnEnable(Tenant $tenant)` | 租户 | 租户侧每次启用 | 无 |
| `tenantOnDisable(Tenant $tenant)` | 租户 | 租户侧每次禁用 | 无 |

## 4. 配置与设置

模块设置基于 `spatie/laravel-settings`，由模块包内定义设置类持久化到**中央库** `settings` 表，运行时通过设置类直接读写，不做 config() 合并。

### 4.1 设置类

模块包内定义两个可选设置类，分别继承框架基类。**设置类自身通过 `schema()` 声明后台表单**（字段名与 public 属性一一对应），一个类即「字段 + 表单」的单一数据源：

```php
use App\Module\Settings\ModulePlatformSettings;
use App\Module\Settings\ModuleTenantSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class BlogPlatformSettings extends ModulePlatformSettings
{
    public int $per_page = 10;          // PHP 默认值即默认设置

    public bool $allow_comments = false;

    public static function groupKey(): string
    {
        return 'lasaas/blog';           // 通常与模块包名一致
    }

    public static function schema(): array
    {
        return [
            TextInput::make('per_page')->label('每页条数')->numeric(),
            Toggle::make('allow_comments')->label('允许评论'),
        ];
    }
}

class BlogTenantSettings extends ModuleTenantSettings
{
    public string $theme_color = '#6366f1';

    public static function groupKey(): string
    {
        return 'lasaas/blog';
    }

    public static function schema(): array
    {
        return [
            TextInput::make('theme_color')->label('主题色'),
        ];
    }
}
```

不覆盖 `schema()` 时默认返回空数组（无后台表单）。

### 4.2 Provider 钩子

在模块的 ServiceProvider（继承 `App\Module\ModuleServiceProvider`）中声明设置类（表单结构由设置类自身的 `schema()` 提供）：

| 方法 | 返回 | 作用 |
| --- | --- | --- |
| `settingsClasses()` | `['platform' => 类名, 'tenant' => 类名]` 映射数组 | 声明模块的所有设置类，用 key 区分设置类型，方便扩展 |

内置 key：

| key | 设置类必须继承 | 作用 |
| --- | --- | --- |
| `platform` | `ModulePlatformSettings` | 平台设置，group 为 `module:{groupKey}`，对所有租户统一生效 |
| `tenant` | `ModuleTenantSettings` | 租户设置，group 为 `tenant_module:{tenant_id}:{groupKey}`，按租户隔离 |

```php
class BlogServiceProvider extends ModuleServiceProvider
{
    public function settingsClasses(): array
    {
        return [
            'platform' => BlogPlatformSettings::class,
            'tenant' => BlogTenantSettings::class,
        ];
    }
}
```

两套 schema 的字段结构允许不同，字段名需与对应设置类的 public 属性一一对应，由模块开发者自行设计。

### 4.3 运行时读取

- 平台设置：`app(ModuleManager::class)->resolvePlatformSettings($module)` 或直接 `app(BlogPlatformSettings::class)`。
- 租户设置（租户上下文已初始化）：直接 `app(BlogTenantSettings::class)`，group 自动指向当前租户。
- 卸载模块/租户模块时，框架自动清理对应的 settings 分组。

> 注意：中央后台管理某个租户的模块设置时，用 `ModuleManager::resolveTenantSettings($module, $tenant)` 显式指定租户。

**租户设置与平台设置的关系是「同名覆盖、其余回退」（不是继承）：**

- 平台设置类声明全部设置项（平台默认值）；租户设置类只声明租户可定制的子集。
- 读取顺序：租户已存值 > 平台设置值（字段同名）> PHP 默认值。
- 保存租户设置时，只有「被显式修改」的字段才写入租户分组；等于回退值的字段不落库，
  因此平台设置修改后，未覆盖的租户会自动跟随新值。

## 5. 扩展点

### 5.1 Filament 后台插件（约定发现）

在模块 PSR-4 根目录的 `Filament/Plugins/` 子目录下实现接口，无需在 composer.json 声明：

- `App\Module\Contracts\AdminPanelPlugin` → 中央 admin 面板
- `App\Module\Contracts\TenantAdminPanelPlugin` → 租户 admin 面板

```php
use App\Module\Contracts\AdminPanelPlugin;
use Filament\Panel;

class AdminBlogPlugin implements AdminPanelPlugin
{
    public function getId(): string
    {
        return 'lasaas-blog'; // 建议按包名派生（/ 转 -），避免插件 ID 重复
    }

    public function register(Panel $panel): void
    {
        $panel->pages([...]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
```

### 5.2 前台个人后台（事件钩子）

中央与租户前台共用两个事件，通过 `$area` 区分上下文：

- `App\Events\FrontendNavigationCollecting`（侧边栏导航）
- `App\Events\FrontendDashboardCardsCollecting`（首页模块入口卡片）

在 ServiceProvider 的 `boot()` 中监听：

```php
use App\Events\FrontendNavigationCollecting;
use Illuminate\Support\Collection;

Event::listen(function (FrontendNavigationCollecting $event) {
    if ($event->area !== 'tenant') {
        return; // 只注入租户个人后台
    }

    $event->items->push([
        'label' => '博客',
        'url' => route('blog.index'),
        'icon' => 'document-text',
        'group' => '内容',
    ]);
});
```

`area` 取值：`'central'`（中央个人后台）| `'tenant'`（租户个人后台）。

### 5.3 路由

- `routes/web.php`：仅 `central` 区域模块加载，用于中央应用（与框架 `routes/web.php` 对应）。
- `routes/tenant.php`：统一挂载 `EnsureTenantAccessible` 等租户中间件组，在租户上下文生效。
- 路由守卫：可用 `module.enabled:{package_name}` 中间件限制仅模块启用时访问。

## 6. 常用命令

| 命令 | 说明 |
| --- | --- |
| `php artisan module:sync [--force] [--soft] [--dry-run]` | 将磁盘模块包同步到数据库 |
| `php artisan module:enable {package}` | 启用模块（首次触发 install） |
| `php artisan module:disable {package}` | 禁用模块 |
| `php artisan module:uninstall {package}` | 卸载模块（回滚中央迁移） |
| `php artisan module:tenant-enable {tenant} {package}` | 为租户安装并启用模块 |
| `php artisan module:tenant-disable {tenant} {package}` | 禁用指定租户的模块 |
| `php artisan module:tenant-uninstall {tenant} {package}` | 从租户卸载模块 |

启用/禁用/卸载及 `module:sync` 后，面板插件发现缓存会自动清除，无需手动处理。
