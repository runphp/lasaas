# Lasaas - Laravel 多租户 SaaS 平台

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel)
![Livewire](https://img.shields.io/badge/Livewire-4.x-FB70A9?style=for-the-badge)
![Filament](https://img.shields.io/badge/Filament-5.x-FDAE4B?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php)
![License](https://img.shields.io/badge/license-MIT-blue?style=for-the-badge)
![Tests](https://img.shields.io/badge/tests-Pest-green?style=for-the-badge)

</div>

## 🔗 在线演示

**演示地址**：[https://lasaas.doulingvip.com/](https://lasaas.doulingvip.com/)

> ⚠️ **项目目前处于早期开发阶段，生产环境请谨慎使用。** 多租户是个深水区——数据库隔离、域名路由、资源调度、租户生命周期管理……每一个环节都有坑。这个系统要做扎实，还有大量工作要做。项目**永久免费开源**，欢迎大家在 [Issues](https://github.com/runphp/lasaas/issues) 提建议、报 Bug、一起完善。一个人的力量有限，社区的合力才是这个项目最大的护城河。

---

## 📖 项目简介

Lasaas 是一个基于 Laravel 生态构建的现代化多租户 SaaS 平台，采用独立数据库隔离方案，为每个租户提供完全独立的数据存储空间。项目整合了 Livewire、Filament、Spatie Permission 和 Stancl/Tenancy 等优秀组件，提供了完整的用户管理、团队协作、权限控制和多租户解决方案。

---

## 🏗️ 核心架构

```
                        ┌──────────────────────────────┐
                        │    中央管理平台            │
                        │  {central-domain}/admin    │
                        │  用户 · 团队 · 租户 · 模块   │
                        │  角色 · 权限 (RBAC)         │
                        └──────────────────────────────┘
                                       │
              ┌────────────────────────┼────────────────────────┐
              ▼                        ▼                        ▼
        ┌──────────┐            ┌──────────┐            ┌──────────────┐
        │  User    │  多对多    │  Team    │  一对多     │  Tenant      │
        │  用户    │◄─────────►│  团队    │──────────►│  租户(客户)   │
        └──────────┘            └──────────┘            └──────────────┘
              │                        │                        │
              │                        │            ┌───────────┼───────────┐
              │                        │            │           │           │
              │                        ▼            ▼           ▼           ▼
              │              ┌──────────────┐ ┌──────────┐ ┌──────────┐
              │              │  个人后台      │ │租户后台 1 │ │租户后台 N │
              └──────────────┤/{team}/       │ │t1.domain │ │tN.domain │
                             │  dashboard    │ │  /admin  │ │  /admin  │
                             │管理团队下租户    │ └──────────┘ └──────────┘
                             └──────────────┘   独立数据库    独立数据库
                                                 模块按需      模块按需
```

| 关系 | 类型 | 说明 |
|------|------|------|
| User ↔ Team | 多对多 | 用户可加入多个团队，团队有多个成员 |
| Team → Tenant | 一对多 | 一个团队管理 N 个租户（客户），一个租户归属一个团队 |
| 中央 → 租户 | 控制 | 中央管理平台决定每个租户 App 可用的功能模块（按模块开关） |

### 三套面板

| 面板 | 路由 | 谁用 |
|------|------|------|
| **中央管理平台** | `{central-domain}/admin` | 全局管控：用户、团队、租户、模块开关。基于 Filament Shield RBAC，可按角色细粒度控制资源、页面、小组件的权限 |
| **个人后台** | `/{team}/dashboard` | 团队成员：管理该团队下的租户及模块功能 |
| **租户后台** | `{tenant-domain}/admin` | 租户内部管理，同样支持 Filament Shield 细粒度 RBAC |

---

## 🚀 应用场景

Lasaas 的核心竞争力就一句话：**一套代码，无限可能**。

多租户 + 独立数据库 + 独立域名的架构，让你只需开发和维护**一套代码**，就能同时服务成百上千个独立站点。运维成本和开发成本断崖式下降。

### 📌 典型应用场景

| 场景 | 说明 | 收益 |
|------|------|------|
| 🕸️ **站群系统** | 每个站点独立数据库、独立域名，统一后台管理，适用于 SEO 站群、行业门户矩阵 | 几百个站点，一个 `git pull` 全部更新 |
| 🏢 **企业官网平台** | 给企业批量建站，每家拥有独立域名、独立数据和后台 | 服务商模式：开发一次，卖给 N 个客户 |
| 🏪 **多品牌电商** | 同一集团旗下多个品牌，各自独立商城，统一管理 | 品牌独立运营，数据安全隔离，后台集中管控 |
| 📱 **SaaS 产品创业** | 快速构建可商用的 SaaS 产品，按租户收费 | 从 0 到上线只需几天，聚焦业务而非架构 |
| 🏬 **加盟连锁门店** | 每家门店独立管理后台、独立数据，总部统一管控 | 门店之间数据隔离，总部一键查看全部门店 |
| 🏫 **多校区/多机构管理** | 教育集团、培训机构旗下多个校区，各自独立运营 | 校区独立，集团统一，互不干扰 |
| 🏘️ **物业/园区管理** | 多个小区或园区，各自管理住户、收费、报修 | 一套系统管 N 个小区，物业公司的最爱 |
| 🕴️ **服务商/外包公司** | 一个项目模板，交付 N 个客户，各自独立部署能力 | 代码复用率 90%+，人均交付效率翻倍 |
| 📰 **自媒体/内容矩阵** | 多个内容站点，各自独立域名和内容体系 | 一套 CMS 撑起整个内容帝国 |
| 🏛️ **政务/机构信息化** | 下级单位各自独立站点，上级统一监管 | 数据物理隔离，符合安全合规要求 |
| 🔧 **行业软件定制** | CRM、ERP、进销存等行业软件，按客户分别部署 | 高度定制化的同时保持代码统一维护 |

### 🤖 AI + 多租户的化学反应

多租户架构天然适合 AI 应用——每个租户的数据是私有的、敏感的、需要隔离的，而 AI 的能力又是通用的。两者结合，催生了大量高价值场景：

| 场景 | AI 能力 | 为什么需要多租户 |
|------|---------|------------------|
| 🧠 **RAG 知识库平台** | 企业上传私有文档，AI 基于文档问答 | 每个企业的知识库绝对隔离，A 公司文档绝不让 B 公司 AI 看到 |
| 💬 **AI 客服机器人** | 每个租户训练自己的客服 Bot，基于自身产品知识 | 不同企业的产品、FAQ、话术完全不同，数据相互保密 |
| ✍️ **AI 内容工厂** | 按品牌生成营销文案、社媒帖子、产品描述 | 每个品牌有自己的 tone & voice、关键词库、合规要求 |
| 📊 **AI 数据分析 SaaS** | 租户上传数据，AI 自动生成洞察、报告、预测 | 企业经营数据极度敏感，必须物理隔离 |
| 🎓 **AI 教育/私教平台** | 每个机构拥有 AI 导师，因材施教 | 学生数据、课程体系、教学进度各校独立 |
| ⚖️ **AI 法律/合同审查** | 律所上传合同模板和案例，AI 辅助审查 | 客户案件信息绝对机密，不容有失 |
| 🏥 **AI 医疗辅助** | 医院基于自身病例库进行 AI 辅助诊断建议 | 患者隐私，法规强制要求数据隔离 |
| 💼 **AI 招聘筛选** | 每个企业的 JD、候选人库、筛选标准各自独立 | 招聘数据涉及薪酬、竞争对手信息，高度敏感 |
| 🏪 **AI 门店经营大脑** | 每家门店获得 AI 销售预测、库存建议、客流分析 | 门店经营数据是核心竞争力，不能共享 |
| 🔍 **AI 内容审核** | 每个平台配置自己的审核规则、敏感词库、AI 策略 | 不同社区的内容红线完全不同 |
| 🌐 **AI 翻译/本地化** | 每个企业有自己的术语库、翻译记忆、品牌词典 | 术语一致性是企业品牌资产，不可混用 |
| 💰 **AI 智能记账** | 每个企业的财务数据由 AI 自动分类、对账、预警 | 财务数据是企业的命脉，隔离是基本要求 |
| 🤖 **AI Agent 工作流** | 每个租户编排自己的 Agent、工具链、自动化流程 | Agent 的配置和上下文是核心竞争力 |
| 🤝 **AI 销售助手** | 每个销售团队的客户跟进策略、话术库、AI 外呼 | 客户资源是销售的生命线，绝不能泄露 |

> **总结**：AI 提供能力，多租户提供信任。当你的 AI 产品需要处理客户的私有数据时，物理隔离的数据库架构不是加分项，而是**准入门槛**。

### 💰 核心优势

- **一套代码，全量服务** —— 几百个客户站点，只需维护一个 Git 仓库，一次更新全部生效
- **数据库物理隔离** —— 每个租户独立数据库，数据安全性拉满，满足等保合规要求
- **独立域名** —— 每个租户可配置自己的域名，完全白标，客户无感知
- **极低成本** —— 一台服务器就能跑几百个租户，无需为每个客户单独部署
- **快速交付** —— Filament 一行命令生成 CRUD，从需求到上线快到飞起

---

## 💡 为什么选择这套技术栈？

Lasaas 不仅是一个 SaaS 脚手架，更是一套 **高效率开发范式**。你完全可以按需裁剪，选择最适合你的开发方式：

### 不需要多租户？直接用中央应用就够了

多租户是可选的。如果项目不需要多租户，直接在中央应用中开发功能即可，无需触碰 Tenancy 相关配置。Lasaas 的核心能力不依赖多租户。

### Livewire + Flux UI：单页面全栈开发，告别前后端分离

Livewire 让你**不用写一行 JavaScript** 就能构建动态交互界面。结合 Flux UI 组件库，表单、模态框、数据列表等常见 UI 都是现成的组件，直接拼装即可。一套 Blade 模板搞定前后端，开发效率提升数倍。

### Filament：增删改查只需一个命令

```
php artisan make:filament-resource Post
```

一行命令生成完整的 CRUD 管理页面，包含列表、表单、筛选、导出等功能。无需手写 Controller、View、Route，Filament 全部自动化完成。

### Filament Shield：权限管理零代码

Shield 会自动扫描你的资源（Resources）、页面（Pages）、组件（Widgets），并为它们生成细粒度权限。在后台点点鼠标就能完成角色和权限的分配，**无需写一行权限代码**。

### Laravel：AI 辅助开发的最佳框架

像 Claude Code、Cursor、GitHub Copilot 这样的 **AI 编码 Agent** 的兴起，彻底改变了开发方式——它们可以以前所未有的速度生成完整功能、调试复杂问题、重构代码。但它们的有效性在很大程度上取决于对代码库的理解程度。

Laravel 约定俗成的惯例和定义良好的结构使其成为 **AI 辅助开发的理想框架**：

- 当你要求 AI Agent 添加一个控制器时，它确切地知道该放在 `app/Http/Controllers` 目录
- 当你需要一个新的迁移时，文件位置和命名约定是可预测的
- Eloquent 关联、表单请求、中间件等功能遵循的模式是 Agent 可以可靠理解和复制的

这种一致性消除了那些在更灵活但更随意的框架中常常困扰 AI 工具的猜测工作。**AI 生成的 Laravel 代码看起来就像是由经验丰富的 Laravel 开发人员编写的**，而不是从通用的 PHP 代码片段拼凑而成的。

#### 🚀 Laravel Boost：让 AI Agent 成为你的 Laravel 专家

[Laravel Boost](https://github.com/laravel/boost) 是一个 MCP（模型上下文协议）服务器，弥合了 AI 编码 Agent 和你的 Laravel 应用之间的鸿沟。安装后，你的 AI Agent 将从通用代码助手转变为**理解你特定应用的 Laravel 专家**。

Boost 提供超过 15 种专用工具：

| 能力 | 说明 |
|------|------|
| **应用内省** | 查询 PHP/Laravel 版本、已安装的包、配置和环境变量 |
| **数据库洞察** | 检查数据库架构、执行只读查询，不离开对话就能理解数据结构 |
| **路由检查** | 列出所有已注册路由及其中间件、控制器和参数 |
| **Artisan 命令** | 发现可用命令及其参数，Agent 可为任务建议并执行正确命令 |
| **日志分析** | 读取和分析应用日志，辅助调试 |
| **Tinker 集成** | 在应用上下文中执行 PHP 代码，让 Agent 测试假设、验证行为 |
| **文档搜索** | 搜索超过 17,000 条 Laravel 生态系统文档，版本精准匹配 |

```bash
composer require laravel/boost --dev
php artisan boost:install
```

**AI 在 Laravel 中写代码，比你想象的更准确、更可靠。**

---

## ✨ 功能介绍

### 🏢 多租户架构
- **独立数据库隔离**：每个租户拥有独立的数据库，确保数据安全和隐私
- **独立域名支持**：每个租户可配置专属域名访问（如 tenant.example.com）
- **自动租户初始化**：创建租户时自动完成数据库创建、迁移等流程
- **资源隔离**：缓存、文件系统、队列等资源按租户隔离
- **租户状态管理**：支持租户激活、过期、禁用等状态管理
- **灵活的数据库驱动**：支持 MySQL、PostgreSQL、SQLite 等多种数据库

### 👤 中央管理平台（Central App）

#### 个人中心（Livewire + Flux UI）
- 用户注册/登录（支持双因素认证、Passkeys 无密码登录）
- 个人资料管理（头像、姓名、邮箱等）
- 安全设置（密码修改、两步验证、会话管理）
- 团队管理
  - 创建和管理多个团队
  - 邀请成员加入团队
  - 角色权限分配（Admin/Member）
  - 团队成员管理（移除、角色变更）
  - 团队切换功能
- 外观偏好设置（主题、语言等）
- 团队邀请链接接受

#### 管理后台（Filament Admin Panel）
- **用户管理**
  - 查看所有注册用户列表
  - 用户状态管理（激活/禁用）
  - 用户详情查看与编辑
  - 用户角色分配
  
- **租户管理**
  - 创建新租户（自动生成数据库）
  - 租户列表查看与筛选
  - 租户域名配置与管理
  - 租户状态管理（激活/过期/禁用）
  - 租户数据统计
  - 租户信息维护（名称、联系方式等）
  
- **团队管理**
  - 全局团队视图
  - 团队数据统计
  - 团队成员查询
  
- **权限管理（Filament Shield）**
  - 角色定义与管理
  - 权限分配与控制
  - 访问控制策略
  - 细粒度权限管理

### 🏠 租户管理平台（Tenant App）

每个租户拥有独立的 Filament 管理后台，通过专属域名访问：

- **用户管理**
  - 租户内部用户 CRUD（创建、读取、更新、删除）
  - 用户角色分配与管理
  - 用户激活/禁用控制
  - 用户数据隔离
  
- **团队管理**
  - 团队信息维护
  - 团队成员管理
  - 团队权限配置
  - 团队邀请管理
  
- **权限管理**
  - 基于 Spatie Permission 的 RBAC（角色基于访问控制）
  - 细粒度权限控制
  - 角色继承与组合
  - 权限中间件保护
  
- **可扩展性**
  - 支持自定义业务模块
  - 租户级别的配置定制
  - 独立的数据库迁移

## 🛠️ 技术栈

### 后端框架
- **Laravel 13.x** - PHP Web 应用框架
- **PHP 8.3+** - 编程语言

### 前端技术
- **Livewire 4.x** - 全栈 Reactivity 框架，无需编写 JavaScript
- **Flux UI 2.x** - 专业的 Livewire 组件库
- **Alpine.js** - 轻量级 JavaScript 框架
- **Tailwind CSS 4.x** - 实用优先的 CSS 框架
- **Vite 8.x** - 现代前端构建工具

### 管理面板
- **Filament 5.x** - Laravel 管理面板构建器
  - Forms - 强大的表单构建器
  - Tables - 数据表格展示与筛选
  - Notifications - 实时通知系统
  - Widgets - 数据可视化组件
  - Filament Shield - 权限管理集成

### 多租户
- **Stancl/Tenancy 3.x** - Laravel 多租户解决方案
  - 数据库自动隔离
  - 域名路由识别
  - 资源自动隔离（缓存、文件系统、队列）
  - 租户生命周期管理

### 权限管理
- **Spatie Laravel Permission 7.x** - 角色和权限管理
  - RBAC（角色基于访问控制）
  - 多模型权限支持
  - 权限缓存优化

### 认证授权
- **Laravel Fortify** - 无头认证后端
  - 双因素认证（2FA）
  - Passkeys 无密码登录支持
  - 邮箱验证
  - 密码重置

### 国际化
- **Laravel Lang** - 多语言支持
  - 中文（简体）
  - 英文
  - 易于扩展其他语言

### 开发工具
- **Pest 4.x** - 优雅的 PHP 测试框架
- **Laravel Pint** - 代码风格修复工具（PHP-CS-Fixer）
- **DDEV** - 本地开发环境管理
- **Laravel Pail** - 日志查看工具
- **Concurrently** - 并行任务执行

## 📁 项目结构

```
lasaas/
├── app/
│   ├── Actions/           # 业务逻辑动作类（Fortify、Teams）
│   ├── Concerns/          # Traits（可复用特性）
│   ├── Enums/             # 枚举类（TeamRole、TeamPermission、TenantStatus）
│   ├── Filament/          # Filament 管理面板资源
│   │   ├── Resources/     # 资源管理（Users、Tenants、Teams、Roles）
│   │   ├── Pages/         # 自定义页面
│   │   └── Widgets/       # 数据小组件
│   ├── Http/              # HTTP 相关（Controllers、Middleware、Responses）
│   ├── Livewire/          # Livewire 组件
│   ├── Models/            # Eloquent 数据模型（User、Team、Tenant、Membership等）
│   ├── Notifications/     # 通知类
│   ├── Policies/          # 授权策略类
│   ├── Providers/         # 服务提供者
│   ├── Rules/             # 自定义验证规则
│   └── Support/           # 辅助类
├── config/                # 配置文件（tenancy、fortify、filament、permission等）
├── database/
│   ├── migrations/        # 中央数据库迁移
│   │   └── tenant/        # 租户数据库迁移模板
│   ├── seeders/           # 数据填充器
│   └── factories/         # 模型工厂（测试用）
├── resources/
│   ├── views/             # Blade 视图模板
│   │   ├── components/    # Blade 组件
│   │   ├── layouts/       # 布局模板
│   │   ├── pages/         # 页面视图（auth、teams、profile）
│   │   └── flux/          # Flux UI 组件覆盖
│   ├── js/                # JavaScript 文件
│   └── css/               # CSS 样式文件
├── routes/                # 路由定义
│   ├── web.php            # 中央应用路由
│   ├── tenant.php         # 租户应用路由
│   ├── settings.php       # 设置相关路由
│   └── console.php        # Artisan 命令路由
├── tests/                 # 测试文件
│   ├── Feature/           # 功能测试
│   └── Unit/              # 单元测试
├── public/                # 公共资源目录（入口文件、构建产物）
├── storage/               # 存储目录（app、framework、logs）
├── lang/                  # 多语言文件（en、zh_CN）
├── .ddev/                 # DDEV 开发环境配置
├── .env.example           # 环境变量示例
├── composer.json          # Composer 依赖配置
├── package.json           # NPM 依赖配置
├── vite.config.js         # Vite 构建配置
└── artisan                # Laravel Artisan 命令行工具
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
ddev artisan livewire:publish --assets
ddev artisan shield:generate --all
ddev artisan db:seed
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
php artisan livewire:publish --assets
php artisan shield:generate --all
php artisan db:seed
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

#### 租户管理
```bash
# 创建新租户
php artisan tenants:create

# 列出所有租户
php artisan tenants:list

# 为所有租户运行迁移
php artisan tenants:migrate

# 为特定租户运行迁移
php artisan tenants:migrate --tenants=demo,prod

# 填充租户数据
php artisan tenants:seed

# 删除租户及其数据库
php artisan tenants:delete {tenant_id}
```

#### 权限管理（Filament Shield）
```bash
# 生成所有资源的权限
php artisan shield:generate --all

# 生成特定资源的权限
php artisan shield:generate --resource=User

# 安装 Shield
php artisan shield:install
```

#### 常规命令
```bash
# 清除缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 缓存优化
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 查看路由列表
php artisan route:list

# 数据库相关
php artisan migrate
php artisan migrate:rollback
php artisan db:seed
php artisan db:wipe

# 队列管理
php artisan queue:work
php artisan queue:restart
php artisan queue:flush
```

### NPM 脚本

```bash
# 开发模式（带热重载）
npm run dev

# 生产构建
npm run build

# 预览生产构建
npm run preview
```

### Composer 脚本

```bash
# 完整初始化项目
composer run setup

# 启动开发环境（服务器+队列+日志+Vite）
composer run dev

# 格式化代码
composer run lint

# 检查代码风格
composer run lint:check

# 运行测试
composer run test

# CI 检查
composer run ci:check
```

### DDEV 命令

```bash
# 启动环境
ddev start

# 停止环境
ddev stop

# 重启环境
ddev restart

# 删除环境（保留数据）
ddev delete

# 进入 Web 容器
ddev ssh

# 查看日志
ddev logs

# 访问数据库
ddev mysql

# 邮件预览
ddev mailhog

# 执行 Composer 命令
ddev composer install

# 执行 NPM 命令
ddev npm run dev

# 执行 Artisan 命令
ddev artisan migrate
```

## 🔐 安全考虑

Lasaas 采用了多层次的安全措施：

- ✅ **数据隔离**：每个租户独立数据库，数据完全隔离
- ✅ **密码加密**：使用 bcrypt 算法加密存储密码
- ✅ **双因素认证**：支持 TOTP 和 Passkeys
- ✅ **CSRF 保护**：所有表单请求自动验证 CSRF token
- ✅ **XSS 防护**：Blade 模板自动转义输出
- ✅ **SQL 注入防护**：Eloquent ORM 使用参数化查询
- ✅ **RBAC 权限控制**：基于角色的访问控制
- ✅ **中间件保护**：租户隔离验证、身份认证中间件
- ✅ **邮箱验证**：新用户必须验证邮箱
- ✅ **会话管理**：支持查看和管理活跃会话
- ✅ **速率限制**：防止暴力破解和 DDoS 攻击

### 安全最佳实践

1. **生产环境配置**
   ```env
   APP_DEBUG=false
   APP_ENV=production
   ```

2. **定期更新依赖**
   ```bash
   composer update
   npm update
   ```

3. **备份策略**
   - 定期备份中央数据库
   - 定期备份所有租户数据库
   - 备份文件系统和用户上传内容

4. **监控和日志**
   - 启用错误监控（如 Sentry）
   - 定期审查日志文件
   - 设置异常报警

## 🤝 贡献指南

欢迎提交 Issue 和 Pull Request！

### 贡献流程

1. **Fork 本仓库**
   点击 GitHub 页面右上角的 Fork 按钮

2. **创建特性分支**
   ```bash
   git checkout -b feature/AmazingFeature
   ```

3. **提交更改**
   ```bash
   git commit -m 'Add some AmazingFeature'
   ```

4. **推送到分支**
   ```bash
   git push origin feature/AmazingFeature
   ```

5. **开启 Pull Request**
   在 GitHub 上创建 Pull Request，描述您的更改

### 代码规范

- 遵循 [PSR-12](https://www.php-fig.org/psr/psr-12/) 编码规范
- 使用 Laravel Pint 格式化代码：`composer run lint`
- 编写测试用例覆盖新功能
- 更新相关文档

### 报告问题

如果您发现了 bug 或有功能建议：

1. 搜索现有 Issues，避免重复报告
2. 创建新的 Issue，详细描述问题
3. 提供重现步骤（如适用）
4. 包含环境信息（PHP 版本、数据库等）

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 🙏 致谢

- [Laravel](https://laravel.com) - Web 应用框架
- [Livewire](https://livewire.laravel.com) - 全栈框架
- [Filament](https://filamentphp.com) - 管理面板
- [Stancl/Tenancy](https://tenancyforlaravel.com) - 多租户解决方案
- [Spatie](https://spatie.be) - Permission 包
- [Flux UI](https://fluxui.dev) - UI 组件库
- [Laravel Lang](https://laravel-lang.com) - 国际化支持
- [Pest](https://pestphp.com) - 测试框架

## ❓ 常见问题（FAQ）

### 一般问题

#### Q: 如何创建第一个管理员账户？
A: 访问注册页面创建账户，然后使用 Tinker 分配角色：
```bash
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $user->assignRole('super_admin');
```

#### Q: 如何重置密码？
A: 在登录页面点击“忘记密码”，输入邮箱地址接收重置链接。

#### Q: 支持哪些数据库？
A: 支持 MySQL 8.0+、MariaDB 10.5+、PostgreSQL 14+ 和 SQLite（开发环境）。

### 多租户相关

#### Q: 如何为新租户配置域名？
A: 在 Filament 管理后台的 Tenants 页面，编辑租户并添加域名。或者使用代码：
```php
\App\Models\Domain::create([
    'domain' => 'tenant.yourdomain.com',
    'tenant_id' => $tenantId,
]);
```

#### Q: 租户数据库在哪里？
A: 每个租户有独立的数据库，命名格式为 `tenant_{tenant_id}`。可以在中央数据库中查看 `tenants` 表获取租户列表。

#### Q: 如何删除租户及其数据？
A: 使用 Artisan 命令：
```bash
php artisan tenants:delete {tenant_id}
```
这会删除租户记录和其独立数据库。

#### Q: 如何在中央应用和租户应用之间切换？
A: 
- 中央应用：访问主域名（如 lasaas.ddev.site）
- 租户应用：访问租户专属域名（如 demo.lasaas.ddev.site）

### 开发相关

#### Q: 如何添加新的租户资源？
A: 参考“开发指南”部分的详细步骤：
1. 在 `database/migrations/tenant/` 创建迁移
2. 创建模型
3. 创建 Filament 资源
4. 运行 `php artisan tenants:migrate`

#### Q: DDEV 启动失败怎么办？
A: 尝试以下步骤：
```bash
# 停止并重新启动
ddev stop
ddev start

# 如果仍有问题，删除并重建
ddev delete
ddev start
```

#### Q: 前端资源更新后没有生效？
A: 清除浏览器缓存并重新构建：
```bash
npm run build
# 或开发模式
npm run dev
```

#### Q: 如何查看队列任务？
A: 检查 `jobs` 表或使用 Horizon（如果安装）：
```bash
php artisan tinker
>>> \Illuminate\Support\Facades\DB::table('jobs')->count();
```

### 权限相关

#### Q: 如何为用户分配角色？
A: 在 Filament 后台的用户管理页面，编辑用户并分配角色。或使用代码：
```php
$user->assignRole('admin');
```

#### Q: Filament Shield 是什么？
A: Filament Shield 是一个为 Filament 资源自动生成权限的包。它会根据资源生成相应的权限（view、create、update、delete 等）。

#### Q: 如何自定义权限？
A: 在服务提供者或 Seeder 中：
```php
use Spatie\Permission\Models\Permission;
Permission::create(['name' => 'custom-permission']);
```

### 性能相关

#### Q: 如何优化生产环境性能？
A: 
1. 启用缓存：`php artisan optimize`
2. 使用 Redis 作为缓存驱动
3. 配置队列 worker
4. 启用 OPcache
5. 使用 CDN 存储静态资源
6. 数据库索引优化

#### Q: 如何处理大量租户？
A: 
- 使用连接池管理数据库连接
- 实施租户分片策略
- 定期清理不活跃租户
- 监控服务器资源使用

### 故障排除

#### Q: 出现 "Class not found" 错误
A: 尝试重新生成自动加载文件：
```bash
composer dump-autoload
```

#### Q: 迁移失败怎么办？
A: 
```bash
# 回滚迁移
php artisan migrate:rollback

# 清除迁移表
php artisan migrate:fresh

# 重新迁移
php artisan migrate
```

#### Q: 邮件发送失败
A: 检查 `.env` 中的邮件配置，查看日志文件：
```bash
tail -f storage/logs/laravel.log
```

#### Q: 如何调试租户相关问题？
A: 
```php
// 在当前请求中获取租户信息
dump(tenant());
dump(tenant('id'));

// 检查是否在租户上下文中
dump(tenancy()->initialized);
```

## 📞 联系方式

如有问题或建议，请提交 Issue 或通过以下方式联系：

- **项目 Issues**: 
  - [Gitee](https://gitee.com/lasaas/lasaas/issues)
  - [Codeberg](https://codeberg.org/lasaas/lasaas/issues)
  - [GitHub](https://github.com/runphp/lasaas/issues)

- **微信**: runphp

## 📚 相关资源

### 学习资源
- [Laravel 官方文档](https://laravel.com/docs)
- [Livewire 官方文档](https://livewire.laravel.com/docs)
- [Filament 官方文档](https://filamentphp.com/docs)
- [Spatie Permission 文档](https://spatie.be/docs/laravel-permission)
- [Stancl/Tenancy 文档](https://tenancyforlaravel.com/docs)

### 社区
- [Laravel China 社区](https://learnku.com/laravel)
- [Laracasts](https://laracasts.com) - 视频课程
- [Stack Overflow - Laravel](https://stackoverflow.com/questions/tagged/laravel)

---

<div align="center">
Made with ❤️ using Laravel
</div>
