# Lasaas - Laravel 多租户 SaaS 平台

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel)
![Livewire](https://img.shields.io/badge/Livewire-4.x-FB70A9?style=for-the-badge)
![Filament](https://img.shields.io/badge/Filament-5.x-FDAE4B?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php)
![License](https://img.shields.io/badge/license-MIT-blue?style=for-the-badge)

</div>

## 📖 项目简介

Lasaas 是一个基于 Laravel 生态构建的现代化多租户 SaaS 平台，采用独立数据库隔离方案，为每个租户提供完全独立的数据存储空间。项目整合了 Livewire、Filament、Spatie Permission 和 Stancl/Tenancy 等优秀组件，提供了完整的用户管理、团队协作、权限控制和多租户解决方案。

## ✨ 核心特性

### 🏢 多租户架构
- **独立数据库隔离**：每个租户拥有独立的数据库，确保数据安全和隐私
- **独立域名支持**：每个租户可配置专属域名访问
- **自动租户初始化**：创建租户时自动完成数据库创建、迁移等流程
- **资源隔离**：缓存、文件系统、队列等资源按租户隔离

### 👤 中央管理平台（Central App）

#### 个人中心（Livewire）
- 用户注册/登录（支持双因素认证、Passkeys）
- 个人资料管理
- 安全设置（密码修改、两步验证）
- 团队管理
  - 创建团队
  - 邀请成员
  - 角色权限分配
  - 团队成员管理
- 外观偏好设置

#### 管理后台（Filament）
- **用户管理**
  - 查看所有注册用户
  - 用户状态管理
  - 用户详情查看
  
- **租户管理**
  - 创建新租户
  - 租户列表查看
  - 租户域名配置
  - 租户数据管理
  
- **团队管理**
  - 全局团队视图
  - 团队数据统计
  
- **权限管理**
  - 角色定义与管理
  - 权限分配
  - 访问控制策略

### 🏠 租户管理平台（Tenant App）

每个租户拥有独立的 Filament 管理后台：

- **用户管理**
  - 租户内部用户 CRUD
  - 用户角色分配
  - 用户激活/禁用
  
- **团队管理**
  - 团队信息维护
  - 团队成员管理
  - 团队权限配置
  
- **权限管理**
  - 基于 Spatie Permission 的 RBAC
  - 细粒度权限控制
  - 角色继承与组合

## 🛠️ 技术栈

### 后端框架
- **Laravel 13.x** - PHP Web 应用框架
- **PHP 8.3+** - 编程语言

### 前端技术
- **Livewire 4.x** - 全栈 Reactivity 框架
- **Flux UI** - Livewire 组件库
- **Alpine.js** - 轻量级 JavaScript 框架
- **Tailwind CSS** - 实用优先的 CSS 框架
- **Vite 8.x** - 现代前端构建工具

### 管理面板
- **Filament 5.x** - Laravel 管理面板构建器
  - Forms - 表单构建
  - Tables - 表格展示
  - Notifications - 通知系统
  - Widgets - 数据可视化组件

### 多租户
- **Stancl/Tenancy 3.x** - Laravel 多租户解决方案
  - 数据库隔离
  - 域名路由
  - 资源隔离

### 权限管理
- **Spatie Laravel Permission** - 角色和权限管理

### 认证授权
- **Laravel Fortify** - 无头认证后端
  - 双因素认证
  - Passkeys 支持
  - 邮箱验证

### 国际化
- **Laravel Lang** - 多语言支持（中文/英文）

### 开发工具
- **Pest** - 优雅的 PHP 测试框架
- **Laravel Pint** - 代码风格修复工具
- **DDEV** - 本地开发环境

## 📁 项目结构

```
lasaas/
├── app/
│   ├── Actions/           # 业务逻辑动作类
│   │   ├── Fortify/      # Fortify 认证动作
│   │   └── Teams/        # 团队相关动作
│   ├── Concerns/         # Traits
│   ├── Enums/            # 枚举类
│   │   ├── TeamRole.php  # 团队角色枚举
│   │   └── TeamPermission.php
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/   # 中间件
│   │   └── Responses/
│   ├── Models/           # 数据模型
│   │   ├── User.php
│   │   ├── Team.php
│   │   ├── Tenant.php    # 租户模型
│   │   └── Membership.php
│   ├── Providers/        # 服务提供者
│   │   ├── AppServiceProvider.php
│   │   ├── FortifyServiceProvider.php
│   │   └── TenancyServiceProvider.php
│   └── Rules/            # 自定义验证规则
├── config/
│   ├── tenancy.php       # 多租户配置
│   ├── fortify.php       # 认证配置
│   └── ...
├── database/
│   ├── migrations/
│   │   ├── tenant/       # 租户数据库迁移
│   │   └── *.php         # 中央数据库迁移
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── components/   # Blade 组件
│   │   ├── layouts/      # 布局模板
│   │   ├── pages/        # 页面视图
│   │   └── flux/         # Flux UI 组件
│   ├── js/
│   └── css/
├── routes/
│   ├── web.php           # 中央应用路由
│   ├── tenant.php        # 租户应用路由
│   ├── settings.php      # 设置相关路由
│   └── console.php
└── tests/
    ├── Feature/          # 功能测试
    └── Unit/             # 单元测试
```

## 🚀 快速开始

### 环境要求

- PHP 8.3+
- Composer
- Node.js & NPM
- MySQL/MariaDB 或 PostgreSQL
- DDEV（可选，推荐用于本地开发）

### 安装步骤

#### 方式一：使用 DDEV（推荐）

1. **克隆项目**
```bash
git clone <repository-url> lasaas
cd lasaas
```

2. **启动 DDEV 环境**
```bash
ddev start
```

3. **安装依赖并初始化**
```bash
ddev composer install
ddev npm install
ddev artisan key:generate
ddev artisan migrate
ddev npm run build
```

4. **访问应用**
- 中央应用：`https://lasaas.ddev.site`
- 租户应用：`https://{tenant-id}.lasaas.ddev.site`

#### 方式二：手动安装

1. **克隆项目并安装依赖**
```bash
git clone <repository-url> lasaas
cd lasaas
composer install
npm install
```

2. **配置环境变量**
```bash
cp .env.example .env
php artisan key:generate
```

编辑 `.env` 文件，配置数据库连接：
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lasaas_central
DB_USERNAME=root
DB_PASSWORD=
```

3. **运行数据库迁移**
```bash
php artisan migrate
```

4. **编译前端资源**
```bash
npm run build
```

5. **启动开发服务器**
```bash
# 方式一：使用 Laravel 内置命令
php artisan serve

# 方式二：使用完整开发环境（推荐）
composer run dev
```

### 初始配置

1. **创建第一个用户**
访问注册页面创建管理员账户

2. **创建第一个租户**
在管理后台或通过 Artisan 命令：
```bash
php artisan tinker
>>> \App\Models\Tenant::create(['id' => 'demo']);
>>> \App\Models\Domain::create(['domain' => 'demo.lasaas.test', 'tenant_id' => 'demo']);
```

3. **运行租户迁移**
```bash
php artisan tenants:migrate
```

## 📝 使用说明

### 中央应用功能

#### 用户注册与登录
- 访问首页进行用户注册
- 支持邮箱验证
- 支持双因素认证（2FA）
- 支持 Passkeys 无密码登录

#### 团队管理
1. **创建团队**
   - 点击团队切换器中的"创建团队"
   - 输入团队名称和 Slug
   - 系统自动生成唯一团队标识

2. **邀请成员**
   - 进入团队设置页面
   - 点击"邀请成员"
   - 输入邮箱地址选择角色
   - 发送邀请邮件

3. **管理成员**
   - 查看团队成员列表
   - 修改成员角色（Admin/Member）
   - 移除团队成员

#### 管理后台
访问 `/admin` 进入 Filament 管理面板：
- 管理所有用户
- 创建和管理租户
- 配置租户域名
- 查看系统统计

### 租户应用功能

#### 访问租户后台
通过配置的域名访问租户应用：
```
https://your-tenant-domain.com/admin
```

#### 租户内部管理
- 管理租户内部用户
- 配置团队权限
- 自定义业务逻辑

## 🔧 开发指南

### 添加新的租户资源

1. **创建迁移文件**
```bash
php artisan make:migration create_posts_table --path=database/migrations/tenant
```

2. **创建模型**
```bash
php artisan make:model Post
```

3. **创建 Filament 资源**
```bash
php artisan make:filament-resource Post --tenant
```

4. **运行租户迁移**
```bash
php artisan tenants:migrate
```

### 自定义权限

1. **定义角色和权限**
```php
// 在服务提供者中
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::create(['name' => 'manager']);
$permission = Permission::create(['name' => 'edit-posts']);
$role->givePermissionTo($permission);
```

2. **在代码中使用**
```php
// 检查权限
$user->can('edit-posts');

// 检查角色
$user->hasRole('manager');
```

### 测试

运行所有测试：
```bash
composer test
```

运行特定测试：
```bash
php artisan test --filter=TeamTest
```

## 📦 可用命令

### Artisan 命令

```bash
# 租户管理
php artisan tenants:create                    # 创建新租户
php artisan tenants:migrate                   # 运行所有租户迁移
php artisan tenants:seed                      # 填充租户数据
php artisan tenants:list                      # 列出所有租户

# 常规命令
php artisan cache:clear                       # 清除缓存
php artisan config:cache                      # 缓存配置
php artisan route:list                        # 查看路由列表
php artisan optimize                          # 优化应用
```

### NPM 脚本

```bash
npm run dev          # 开发模式（带热重载）
npm run build        # 生产构建
npm run preview      # 预览生产构建
```

### Composer 脚本

```bash
composer run setup   # 完整初始化项目
composer run dev     # 启动开发环境
composer run lint    # 格式化代码
composer run test    # 运行测试
```

## 🔐 安全考虑

- ✅ 每个租户独立数据库，数据完全隔离
- ✅ 密码加密存储（bcrypt）
- ✅ 支持双因素认证
- ✅ CSRF 保护
- ✅ XSS 防护
- ✅ SQL 注入防护
- ✅ 基于角色的访问控制（RBAC）
- ✅ 中间件级别的租户隔离验证

## 🤝 贡献指南

欢迎提交 Issue 和 Pull Request！

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 🙏 致谢

- [Laravel](https://laravel.com) - Web 应用框架
- [Livewire](https://livewire.laravel.com) - 全栈框架
- [Filament](https://filamentphp.com) - 管理面板
- [Stancl/Tenancy](https://tenancyforlaravel.com) - 多租户解决方案
- [Spatie](https://spatie.be) - Permission 包
- [Flux UI](https://fluxui.dev) - UI 组件库

## 📞 联系方式

如有问题或建议，请提交 Issue 或通过以下方式联系：

- 项目 Issues: 
  - [Gitee](https://gitee.com/lasaas/lasaas/issues)
  - [Codeberg](https://codeberg.org/lasaas/lasaas/issues)
  - [Github](https://github.com/runphp/lasaas/issues)


---

<div align="center">
Made with ❤️ using Laravel
</div>
