<?php

use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::landing')] class extends Component
{
    public string $locale;

    public function mount(): void
    {
        $this->locale = app()->getLocale();
        $page = Page::findBySlug('home');
        view()->share('title', $page?->title ?? __('Lasaas - Laravel Multi-Tenant SaaS Platform'));
    }

    public function t(string $key): string
    {
        return $this->texts()[$key][$this->locale] ?? $key;
    }

    private function texts(): array
    {
        return [
            'badge_open_source' => [
                'zh-CN' => '永久免费开源 · MIT 协议',
                'en' => 'Free & Open Source · MIT License',
            ],
            'hero_heading_line1' => [
                'zh-CN' => '一套代码',
                'en' => 'One Codebase',
            ],
            'hero_heading_line2' => [
                'zh-CN' => '无限可能',
                'en' => 'Infinite Possibilities',
            ],
            'hero_subtitle' => [
                'zh-CN' => '基于 Laravel 生态的现代化多租户 SaaS 平台。独立数据库隔离、自定义域名、团队协作、RBAC 权限体系——从站群系统到 AI 知识库，开箱即用，极速交付。',
                'en' => 'A modern multi-tenant SaaS platform built on the Laravel ecosystem. Isolated databases, custom domains, team collaboration, RBAC permission system — from site networks to AI knowledge bases, ready to use, rapid delivery.',
            ],
            'btn_start_free' => [
                'zh-CN' => '免费开始使用',
                'en' => 'Start Free',
            ],
            'btn_pricing' => [
                'zh-CN' => '查看定价',
                'en' => 'Pricing',
            ],
            'section_core_badge' => [
                'zh-CN' => '核心架构',
                'en' => 'Core Architecture',
            ],
            'section_core_heading' => [
                'zh-CN' => '多租户架构，数据物理隔离',
                'en' => 'Multi-Tenant Architecture, Physical Data Isolation',
            ],
            'section_core_desc' => [
                'zh-CN' => '每个租户拥有独立数据库和专属域名，从底层保障数据安全与隐私合规。',
                'en' => 'Each tenant has an independent database and dedicated domain, ensuring data security and privacy compliance at the foundational level.',
            ],
            'feature_db_title' => [
                'zh-CN' => '独立数据库',
                'en' => 'Isolated Database',
            ],
            'feature_db_desc' => [
                'zh-CN' => '每个租户独立数据库，数据物理隔离，满足等保合规要求',
                'en' => 'Each tenant has an independent database with physical data isolation, meeting compliance requirements',
            ],
            'feature_domain_title' => [
                'zh-CN' => '独立域名',
                'en' => 'Custom Domain',
            ],
            'feature_domain_desc' => [
                'zh-CN' => '每个租户可绑定专属域名，完全白标，客户无感知',
                'en' => 'Each tenant can bind a custom domain, fully white-labeled, seamless for customers',
            ],
            'feature_team_title' => [
                'zh-CN' => '团队管辖',
                'en' => 'Team Management',
            ],
            'feature_team_desc' => [
                'zh-CN' => '用户可以在多个团队间切换，每个团队独立管理自己的租户',
                'en' => 'Users can switch between multiple teams, each independently managing their own tenants',
            ],
            'feature_module_title' => [
                'zh-CN' => '模块开关',
                'en' => 'Module Toggles',
            ],
            'feature_module_desc' => [
                'zh-CN' => '中央管理平台可精细控制每个租户 App 可用的功能模块',
                'en' => 'Central management platform can finely control available feature modules for each tenant app',
            ],
            'section_features_badge' => [
                'zh-CN' => '强大特性',
                'en' => 'Powerful Features',
            ],
            'section_features_heading' => [
                'zh-CN' => '开箱即用的全栈能力',
                'en' => 'Full-Stack Capabilities Out of the Box',
            ],
            'section_features_desc' => [
                'zh-CN' => 'Filament 一行命令生成 CRUD，Filament Shield 零代码分配权限——从零到上线只需几天。',
                'en' => 'Generate CRUD with a single Filament command, assign permissions with Filament Shield without code — from zero to launch in just days.',
            ],
            'feature_filament_title' => [
                'zh-CN' => 'Filament 管理面板',
                'en' => 'Filament Admin Panel',
            ],
            'feature_filament_desc' => [
                'zh-CN' => '强大的管理面板，统一管理用户、团队、租户、角色和权限，支持自定义扩展',
                'en' => 'Powerful admin panel for unified management of users, teams, tenants, roles and permissions, with custom extension support',
            ],
            'feature_livewire_title' => [
                'zh-CN' => 'Livewire + Flux UI',
                'en' => 'Livewire + Flux UI',
            ],
            'feature_livewire_desc' => [
                'zh-CN' => '零 JavaScript 实现全栈响应式交互，Flux 组件库开箱即用，开发效率数倍提升',
                'en' => 'Full-stack reactive interactions with zero JavaScript, Flux component library ready out of the box, multiply development efficiency',
            ],
            'feature_shield_title' => [
                'zh-CN' => 'Filament Shield RBAC',
                'en' => 'Filament Shield RBAC',
            ],
            'feature_shield_desc' => [
                'zh-CN' => '自动扫描资源、页面、小组件生成细粒度权限，后台点点鼠标即可完成角色分配',
                'en' => 'Auto-scan resources, pages, widgets to generate fine-grained permissions, assign roles with just a few clicks',
            ],
            'feature_collab_title' => [
                'zh-CN' => '团队协作',
                'en' => 'Team Collaboration',
            ],
            'feature_collab_desc' => [
                'zh-CN' => '创建团队、邀请成员、分配角色，团队之间数据隔离，权限管理内置',
                'en' => 'Create teams, invite members, assign roles, data isolation between teams, built-in permission management',
            ],
            'feature_auth_title' => [
                'zh-CN' => '认证与安全',
                'en' => 'Auth & Security',
            ],
            'feature_auth_desc' => [
                'zh-CN' => '双因素认证、Passkeys 无密码登录、CSRF/XSS/SQL 注入防护，全方位安全保障',
                'en' => 'Two-factor authentication, Passkeys passwordless login, CSRF/XSS/SQL injection protection, comprehensive security',
            ],
            'feature_i18n_title' => [
                'zh-CN' => '国际化',
                'en' => 'Internationalization',
            ],
            'feature_i18n_desc' => [
                'zh-CN' => '内置中英文双语支持，轻松扩展更多语言',
                'en' => 'Built-in Chinese/English bilingual support, easily extend to more languages',
            ],
            'section_scenarios_badge' => [
                'zh-CN' => '业务场景',
                'en' => 'Use Cases',
            ],
            'section_scenarios_heading' => [
                'zh-CN' => '覆盖多种业务场景',
                'en' => 'Covering Diverse Business Scenarios',
            ],
            'section_scenarios_desc' => [
                'zh-CN' => '多租户 + 独立数据库 + 独立域名的架构，一套代码服务成百上千个独立站点。',
                'en' => 'Multi-tenant + isolated database + custom domain architecture, one codebase serving hundreds or thousands of independent sites.',
            ],
            'scenario_site_network_title' => [
                'zh-CN' => '站群系统',
                'en' => 'Site Network',
            ],
            'scenario_site_network_desc' => [
                'zh-CN' => '几百个站点，一个 git pull 全部更新',
                'en' => 'Hundreds of sites, one git pull updates them all',
            ],
            'scenario_enterprise_title' => [
                'zh-CN' => '企业官网平台',
                'en' => 'Enterprise Website Platform',
            ],
            'scenario_enterprise_desc' => [
                'zh-CN' => '服务商模式：开发一次，卖给 N 个客户',
                'en' => 'Agency model: develop once, sell to N clients',
            ],
            'scenario_ecommerce_title' => [
                'zh-CN' => '多品牌电商',
                'en' => 'Multi-Brand E-Commerce',
            ],
            'scenario_ecommerce_desc' => [
                'zh-CN' => '品牌独立运营，数据安全隔离，集中管控',
                'en' => 'Independent brand operation, secure data isolation, centralized management',
            ],
            'scenario_campus_title' => [
                'zh-CN' => '多校区管理',
                'en' => 'Multi-Campus Management',
            ],
            'scenario_campus_desc' => [
                'zh-CN' => '校区独立，集团统一，互不干扰',
                'en' => 'Campuses independent, unified group management, no interference',
            ],
            'scenario_saas_title' => [
                'zh-CN' => 'SaaS 产品创业',
                'en' => 'SaaS Startup',
            ],
            'scenario_saas_desc' => [
                'zh-CN' => '从 0 到上线只需几天，聚焦业务而非架构',
                'en' => 'From zero to launch in days, focus on business not architecture',
            ],
            'scenario_property_title' => [
                'zh-CN' => '物业/园区管理',
                'en' => 'Property/Park Management',
            ],
            'scenario_property_desc' => [
                'zh-CN' => '一套系统管 N 个小区，物业公司的最爱',
                'en' => 'One system manages N communities, a property company\'s favorite',
            ],
            'scenario_content_title' => [
                'zh-CN' => '内容矩阵',
                'en' => 'Content Matrix',
            ],
            'scenario_content_desc' => [
                'zh-CN' => '一套 CMS 撑起整个内容帝国',
                'en' => 'One CMS powers your entire content empire',
            ],
            'scenario_industry_title' => [
                'zh-CN' => '行业软件定制',
                'en' => 'Industry Software Customization',
            ],
            'scenario_industry_desc' => [
                'zh-CN' => 'CRM、ERP 等按客户分别部署，代码统一维护',
                'en' => 'CRM, ERP etc. deployed per client, unified code maintenance',
            ],
            'section_ai_badge' => [
                'zh-CN' => 'AI 赋能',
                'en' => 'AI Empowered',
            ],
            'section_ai_heading' => [
                'zh-CN' => 'AI + 多租户的化学反应',
                'en' => 'The Chemical Reaction of AI + Multi-Tenant',
            ],
            'section_ai_desc' => [
                'zh-CN' => '每个租户的数据是私有的、需要隔离的，而 AI 的能力又是通用的——两者结合，催生高价值场景。',
                'en' => 'Each tenant\'s data is private and needs isolation, while AI capabilities are universal — combining the two creates high-value scenarios.',
            ],
            'ai_rag_title' => [
                'zh-CN' => 'RAG 知识库平台',
                'en' => 'RAG Knowledge Base Platform',
            ],
            'ai_rag_desc' => [
                'zh-CN' => '企业私有文档问答，知识库绝对隔离',
                'en' => 'Enterprise private document Q&A, absolute knowledge base isolation',
            ],
            'ai_customer_service_title' => [
                'zh-CN' => 'AI 客服机器人',
                'en' => 'AI Customer Service Bot',
            ],
            'ai_customer_service_desc' => [
                'zh-CN' => '每个租户基于自身知识独立训练',
                'en' => 'Each tenant independently trained on their own knowledge',
            ],
            'ai_content_title' => [
                'zh-CN' => 'AI 内容工厂',
                'en' => 'AI Content Factory',
            ],
            'ai_content_desc' => [
                'zh-CN' => '按品牌生成文案，tone&voice 独立配置',
                'en' => 'Generate copy by brand, independent tone & voice configuration',
            ],
            'ai_analytics_title' => [
                'zh-CN' => 'AI 数据分析',
                'en' => 'AI Data Analytics',
            ],
            'ai_analytics_desc' => [
                'zh-CN' => '租户上传数据，AI 生成洞察和预测',
                'en' => 'Tenants upload data, AI generates insights and predictions',
            ],
            'ai_agent_title' => [
                'zh-CN' => 'AI Agent 工作流',
                'en' => 'AI Agent Workflow',
            ],
            'ai_agent_desc' => [
                'zh-CN' => '每个租户编排自己的 Agent 和工具链',
                'en' => 'Each tenant orchestrates their own agents and tool chains',
            ],
            'ai_accounting_title' => [
                'zh-CN' => 'AI 智能记账',
                'en' => 'AI Smart Bookkeeping',
            ],
            'ai_accounting_desc' => [
                'zh-CN' => '财务数据自动分类对账，物理隔离',
                'en' => 'Automatic financial data classification and reconciliation, physical isolation',
            ],
            'section_cta_badge' => [
                'zh-CN' => '快速开始',
                'en' => 'Quick Start',
            ],
            'section_cta_heading' => [
                'zh-CN' => '立即体验',
                'en' => 'Try It Now',
            ],
            'section_cta_desc' => [
                'zh-CN' => '在线演示环境已就绪，无需部署，即刻体验全功能。',
                'en' => 'Online demo environment is ready, no deployment needed, experience all features now.',
            ],
            'demo_title' => [
                'zh-CN' => '在线演示',
                'en' => 'Live Demo',
            ],
            'demo_desc' => [
                'zh-CN' => '登录即可体验完整功能',
                'en' => 'Log in to experience all features',
            ],
            'demo_url_label' => [
                'zh-CN' => '演示地址',
                'en' => 'Demo URL',
            ],
            'demo_account_label' => [
                'zh-CN' => '管理员账号',
                'en' => 'Admin Account',
            ],
            'demo_btn' => [
                'zh-CN' => '进入演示环境',
                'en' => 'Enter Demo',
            ],
            'opensource_title' => [
                'zh-CN' => '开源地址',
                'en' => 'Open Source',
            ],
            'opensource_desc' => [
                'zh-CN' => '查看源码，Star 支持我们',
                'en' => 'View source code, Star to support us',
            ],
            'opensource_repo_label' => [
                'zh-CN' => '仓库地址',
                'en' => 'Repository',
            ],
            'opensource_license_label' => [
                'zh-CN' => '许可证',
                'en' => 'License',
            ],
            'opensource_license_value' => [
                'zh-CN' => 'MIT · 永久免费',
                'en' => 'MIT · Free Forever',
            ],
            'opensource_btn' => [
                'zh-CN' => '查看 GitHub',
                'en' => 'View GitHub',
            ],
            'bottom_cta_heading' => [
                'zh-CN' => '准备好构建你的 SaaS 了吗？',
                'en' => 'Ready to Build Your SaaS?',
            ],
            'bottom_cta_desc' => [
                'zh-CN' => '克隆项目，一条命令初始化，明天就能给你的客户演示。',
                'en' => 'Clone the project, initialize with one command, demo to your clients tomorrow.',
            ],
            'bottom_cta_btn' => [
                'zh-CN' => '免费注册，立即开始',
                'en' => 'Sign Up Free, Start Now',
            ],
            'bottom_cta_pricing' => [
                'zh-CN' => '查看定价方案',
                'en' => 'View Pricing Plans',
            ],
        ];
    }
}; ?>

<flux:main>

    {{-- Hero Section --}}
    <section class="relative overflow-hidden pt-28 pb-20">
        {{-- Decorative background --}}
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[600px] rounded-full bg-gradient-to-b from-blue-100/60 via-indigo-50/30 to-transparent blur-3xl dark:from-blue-900/20 dark:via-indigo-900/10"></div>
            <div class="absolute top-20 right-0 w-[400px] h-[400px] rounded-full bg-gradient-to-l from-purple-100/40 to-transparent blur-3xl dark:from-purple-900/10"></div>
        </div>

        <div class="relative mx-auto max-w-6xl px-4 text-center sm:px-6 lg:px-8">
            {{-- Badge --}}
            <flux:badge color="blue" class="mb-8 !rounded-full !px-4 !py-1.5 !text-sm">
                <span class="relative flex size-2 mr-1">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex size-2 rounded-full bg-green-500"></span>
                </span>
                {{ $this->t('badge_open_source') }}
            </flux:badge>

            {{-- Main heading --}}
            <flux:heading size="xl" level="1" class="!text-center">
                {{ $this->t('hero_heading_line1') }}
                <br class="sm:hidden" />
                <span class="relative inline-block">
                    <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">{{ $this->t('hero_heading_line2') }}</span>
                    <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 200 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 4 Q50 0 100 4 Q150 8 200 4" stroke="currentColor" stroke-width="2" class="text-indigo-300/60 dark:text-indigo-600/40" stroke-linecap="round"/>
                    </svg>
                </span>
            </flux:heading>

            <flux:text class="mx-auto mt-8 max-w-3xl !text-lg !leading-relaxed">
                {{ $this->t('hero_subtitle') }}
            </flux:text>

            {{-- CTA Buttons --}}
            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <flux:button variant="primary" href="{{ route('register') }}" wire:navigate class="!h-12 !text-base !rounded-xl !px-8 !font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-shadow">
                    {{ $this->t('btn_start_free') }}
                    <flux:icon.arrow-right class="size-4 ml-1" />
                </flux:button>
                <flux:button variant="outline" href="{{ localizedRoute('pricing', ['locale' => app()->getLocale()]) }}" wire:navigate class="!h-12 !text-base !rounded-xl">
                    {{ $this->t('btn_pricing') }}
                </flux:button>
                <flux:button variant="ghost" href="https://github.com/runphp/lasaas" target="_blank" class="!h-12 !text-base !rounded-xl">
                    <flux:icon.code-bracket class="size-4" />
                    GitHub
                </flux:button>
            </div>

        </div>
    </section>

    {{-- Core Advantages --}}
    <section class="py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="blue" class="!rounded-full !px-3 !py-1 !text-xs">{{ $this->t('section_core_badge') }}</flux:badge>
                <flux:heading size="lg" class="mt-6">{{ $this->t('section_core_heading') }}</flux:heading>
                <flux:text class="mt-4">{{ $this->t('section_core_desc') }}</flux:text>
            </div>
            <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <flux:card class="group relative overflow-hidden !p-8 transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-500/5 dark:hover:border-blue-700">
                    <div class="absolute top-0 right-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-blue-100/50 opacity-0 blur-2xl transition-opacity group-hover:opacity-100 dark:bg-blue-900/30"></div>
                    <div class="relative flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/20">
                        <flux:icon.database class="size-7 text-zinc-900 dark:text-white" />
                    </div>
                    <flux:heading class="mt-6">{{ $this->t('feature_db_title') }}</flux:heading>
                    <flux:text class="mt-3">{{ $this->t('feature_db_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group relative overflow-hidden !p-8 transition-all duration-300 hover:-translate-y-1 hover:border-green-300 hover:shadow-xl hover:shadow-green-500/5 dark:hover:border-green-700">
                    <div class="absolute top-0 right-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-green-100/50 opacity-0 blur-2xl transition-opacity group-hover:opacity-100 dark:bg-green-900/30"></div>
                    <div class="relative flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg shadow-green-500/20">
                        <flux:icon.globe class="size-7 text-zinc-900 dark:text-white" />
                    </div>
                    <flux:heading class="mt-6">{{ $this->t('feature_domain_title') }}</flux:heading>
                    <flux:text class="mt-3">{{ $this->t('feature_domain_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group relative overflow-hidden !p-8 transition-all duration-300 hover:-translate-y-1 hover:border-purple-300 hover:shadow-xl hover:shadow-purple-500/5 dark:hover:border-purple-700">
                    <div class="absolute top-0 right-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-purple-100/50 opacity-0 blur-2xl transition-opacity group-hover:opacity-100 dark:bg-purple-900/30"></div>
                    <div class="relative flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 shadow-lg shadow-purple-500/20">
                        <flux:icon.users class="size-7 text-zinc-900 dark:text-white" />
                    </div>
                    <flux:heading class="mt-6">{{ $this->t('feature_team_title') }}</flux:heading>
                    <flux:text class="mt-3">{{ $this->t('feature_team_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group relative overflow-hidden !p-8 transition-all duration-300 hover:-translate-y-1 hover:border-amber-300 hover:shadow-xl hover:shadow-amber-500/5 dark:hover:border-amber-700">
                    <div class="absolute top-0 right-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-amber-100/50 opacity-0 blur-2xl transition-opacity group-hover:opacity-100 dark:bg-amber-900/30"></div>
                    <div class="relative flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/20">
                        <flux:icon.blocks class="size-7 text-zinc-900 dark:text-white" />
                    </div>
                    <flux:heading class="mt-6">{{ $this->t('feature_module_title') }}</flux:heading>
                    <flux:text class="mt-3">{{ $this->t('feature_module_desc') }}</flux:text>
                </flux:card>
            </div>
        </div>
    </section>

    {{-- Features Grid --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="purple" class="!rounded-full !px-3 !py-1 !text-xs">{{ $this->t('section_features_badge') }}</flux:badge>
                <flux:heading size="lg" class="mt-6">{{ $this->t('section_features_heading') }}</flux:heading>
                <flux:text class="mt-4">{{ $this->t('section_features_desc') }}</flux:text>
            </div>
            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-lg dark:hover:border-amber-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 dark:from-amber-900/50 dark:to-amber-800/50">
                        <flux:icon.squares-plus class="size-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('feature_filament_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('feature_filament_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-pink-200 hover:shadow-lg dark:hover:border-pink-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-pink-100 to-rose-200 dark:from-pink-900/50 dark:to-rose-800/50">
                        <flux:icon.bolt class="size-6 text-pink-600 dark:text-pink-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('feature_livewire_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('feature_livewire_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-purple-200 hover:shadow-lg dark:hover:border-purple-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-100 to-violet-200 dark:from-purple-900/50 dark:to-violet-800/50">
                        <flux:icon.shield-check class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('feature_shield_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('feature_shield_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-green-200 hover:shadow-lg dark:hover:border-green-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900/50 dark:to-emerald-800/50">
                        <flux:icon.user-group class="size-6 text-green-600 dark:text-green-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('feature_collab_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('feature_collab_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-rose-200 hover:shadow-lg dark:hover:border-rose-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-100 to-pink-200 dark:from-rose-900/50 dark:to-pink-800/50">
                        <flux:icon.lock-closed class="size-6 text-rose-600 dark:text-rose-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('feature_auth_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('feature_auth_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg dark:hover:border-orange-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-orange-100 to-amber-200 dark:from-orange-900/50 dark:to-amber-800/50">
                        <flux:icon.language class="size-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('feature_i18n_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('feature_i18n_desc') }}</flux:text>
                </flux:card>
            </div>
        </div>
    </section>

    {{-- Applicable Scenarios --}}
    <section class="py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="green" class="!rounded-full !px-3 !py-1 !text-xs">{{ $this->t('section_scenarios_badge') }}</flux:badge>
                <flux:heading size="lg" class="mt-6">{{ $this->t('section_scenarios_heading') }}</flux:heading>
                <flux:text class="mt-4">{{ $this->t('section_scenarios_desc') }}</flux:text>
            </div>
            <div class="mt-16 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- 站群系统 --}}
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg dark:hover:border-blue-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/50 dark:to-blue-800/50">
                        <flux:icon.globe-alt class="size-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('scenario_site_network_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('scenario_site_network_desc') }}</flux:text>
                </flux:card>
                {{-- 企业官网平台 --}}
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg dark:hover:border-indigo-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-200 dark:from-indigo-900/50 dark:to-indigo-800/50">
                        <flux:icon.building-office class="size-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('scenario_enterprise_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('scenario_enterprise_desc') }}</flux:text>
                </flux:card>
                {{-- 多品牌电商 --}}
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-lg dark:hover:border-amber-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 dark:from-amber-900/50 dark:to-amber-800/50">
                        <flux:icon.shopping-cart class="size-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('scenario_ecommerce_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('scenario_ecommerce_desc') }}</flux:text>
                </flux:card>
                {{-- 多校区管理 --}}
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-green-200 hover:shadow-lg dark:hover:border-green-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/50 dark:to-green-800/50">
                        <flux:icon.academic-cap class="size-6 text-green-600 dark:text-green-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('scenario_campus_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('scenario_campus_desc') }}</flux:text>
                </flux:card>
                {{-- SaaS 产品创业 --}}
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-purple-200 hover:shadow-lg dark:hover:border-purple-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/50 dark:to-purple-800/50">
                        <flux:icon.cube class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('scenario_saas_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('scenario_saas_desc') }}</flux:text>
                </flux:card>
                {{-- 物业/园区管理 --}}
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-rose-200 hover:shadow-lg dark:hover:border-rose-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-100 to-rose-200 dark:from-rose-900/50 dark:to-rose-800/50">
                        <flux:icon.home-modern class="size-6 text-rose-600 dark:text-rose-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('scenario_property_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('scenario_property_desc') }}</flux:text>
                </flux:card>
                {{-- 内容矩阵 --}}
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-teal-200 hover:shadow-lg dark:hover:border-teal-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-100 to-teal-200 dark:from-teal-900/50 dark:to-teal-800/50">
                        <flux:icon.newspaper class="size-6 text-teal-600 dark:text-teal-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('scenario_content_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('scenario_content_desc') }}</flux:text>
                </flux:card>
                {{-- 行业软件定制 --}}
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-zinc-200 hover:shadow-lg dark:hover:border-zinc-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800/50 dark:to-zinc-700/50">
                        <flux:icon.cog class="size-6 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <flux:heading class="mt-5">{{ $this->t('scenario_industry_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('scenario_industry_desc') }}</flux:text>
                </flux:card>
            </div>
        </div>
    </section>

    {{-- AI + Multi-Tenant --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="violet" class="!rounded-full !px-3 !py-1 !text-xs">{{ $this->t('section_ai_badge') }}</flux:badge>
                <flux:heading size="lg" class="mt-6">{{ $this->t('section_ai_heading') }}</flux:heading>
                <flux:text class="mt-4">{{ $this->t('section_ai_desc') }}</flux:text>
            </div>
            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg dark:hover:border-blue-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-indigo-200 dark:from-blue-900/50 dark:to-indigo-800/50">
                        <flux:icon.brain class="size-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <flux:heading class="mt-5 !text-base">{{ $this->t('ai_rag_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('ai_rag_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-green-300 hover:shadow-lg dark:hover:border-green-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900/50 dark:to-emerald-800/50">
                        <flux:icon.message-circle class="size-6 text-green-600 dark:text-green-400" />
                    </div>
                    <flux:heading class="mt-5 !text-base">{{ $this->t('ai_customer_service_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('ai_customer_service_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-purple-300 hover:shadow-lg dark:hover:border-purple-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-100 to-violet-200 dark:from-purple-900/50 dark:to-violet-800/50">
                        <flux:icon.pen-line class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <flux:heading class="mt-5 !text-base">{{ $this->t('ai_content_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('ai_content_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg dark:hover:border-amber-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-200 dark:from-amber-900/50 dark:to-orange-800/50">
                        <flux:icon.chart-bar class="size-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <flux:heading class="mt-5 !text-base">{{ $this->t('ai_analytics_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('ai_analytics_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-rose-300 hover:shadow-lg dark:hover:border-rose-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-100 to-pink-200 dark:from-rose-900/50 dark:to-pink-800/50">
                        <flux:icon.bot class="size-6 text-rose-600 dark:text-rose-400" />
                    </div>
                    <flux:heading class="mt-5 !text-base">{{ $this->t('ai_agent_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('ai_agent_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-teal-300 hover:shadow-lg dark:hover:border-teal-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-100 to-cyan-200 dark:from-teal-900/50 dark:to-cyan-800/50">
                        <flux:icon.coins class="size-6 text-teal-600 dark:text-teal-400" />
                    </div>
                    <flux:heading class="mt-5 !text-base">{{ $this->t('ai_accounting_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('ai_accounting_desc') }}</flux:text>
                </flux:card>
            </div>
        </div>
    </section>

    {{-- Demo + Open Source CTA --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="blue" class="!rounded-full !px-3 !py-1 !text-xs">{{ $this->t('section_cta_badge') }}</flux:badge>
                <flux:heading size="lg" class="mt-6">{{ $this->t('section_cta_heading') }}</flux:heading>
                <flux:text class="mt-4">{{ $this->t('section_cta_desc') }}</flux:text>
            </div>
            <div class="mt-12 grid gap-8 sm:grid-cols-2">
                <div class="group rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50/50 to-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl dark:border-blue-800/60 dark:from-blue-950/30 dark:to-zinc-900 dark:hover:border-blue-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/25">
                        <flux:icon.circle-user-round class="size-6 text-zinc-900 dark:text-white" />
                    </div>
                    <flux:heading class="mt-6 !text-xl">{{ $this->t('demo_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('demo_desc') }}</flux:text>
                    <div class="mt-6 space-y-3 rounded-xl bg-zinc-900/[0.03] p-5 backdrop-blur-sm dark:bg-white/[0.03]">
                        <div class="flex flex-col items-start gap-1 text-sm sm:flex-row sm:items-center sm:justify-between">
                            <span class="shrink-0 text-zinc-500">{{ $this->t('demo_url_label') }}</span>
                            <code class="rounded-lg bg-blue-500/[0.08] px-3 py-1 text-xs font-medium text-blue-600 dark:text-blue-400">lasaas.doulingvip.com</code>
                        </div>
                        <div class="flex flex-col items-start gap-1 text-sm sm:flex-row sm:items-center sm:justify-between">
                            <span class="shrink-0 text-zinc-500">{{ $this->t('demo_account_label') }}</span>
                            <code class="rounded-lg bg-blue-500/[0.08] px-3 py-1 text-xs font-medium text-blue-600 dark:text-blue-400">runphp@qq.com / 123456789</code>
                        </div>
                    </div>
                    <flux:button class="mt-6 w-full !h-11 !rounded-xl !font-semibold" variant="primary" href="https://lasaas.doulingvip.com/login" target="_blank">
                        {{ $this->t('demo_btn') }}
                        <flux:icon.arrow-right class="size-4 ml-1" />
                    </flux:button>
                </div>
                <div class="group rounded-2xl border border-zinc-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-zinc-300 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-zinc-700 to-zinc-900 shadow-lg shadow-zinc-500/20 dark:from-zinc-600 dark:to-zinc-800">
                        <flux:icon.code class="size-6 text-zinc-900 dark:text-white" />
                    </div>
                    <flux:heading class="mt-6 !text-xl">{{ $this->t('opensource_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $this->t('opensource_desc') }}</flux:text>
                    <div class="mt-6 space-y-3 rounded-xl bg-zinc-50/80 p-5 dark:bg-zinc-800/80">
                        <div class="flex flex-col items-start gap-1 text-sm sm:flex-row sm:items-center sm:justify-between">
                            <span class="shrink-0 text-zinc-400 dark:text-zinc-500">{{ $this->t('opensource_repo_label') }}</span>
                            <code class="rounded-lg bg-zinc-200 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">github.com/runphp/lasaas</code>
                        </div>
                        <div class="flex flex-col items-start gap-1 text-sm sm:flex-row sm:items-center sm:justify-between">
                            <span class="shrink-0 text-zinc-400 dark:text-zinc-500">{{ $this->t('opensource_license_label') }}</span>
                            <span class="rounded-lg bg-green-50 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-300">{{ $this->t('opensource_license_value') }}</span>
                        </div>
                    </div>
                    <flux:button class="mt-6 w-full !h-11 !rounded-xl" variant="outline" href="https://github.com/runphp/lasaas" target="_blank">
                        <flux:icon.code-bracket class="size-4" />
                        {{ $this->t('opensource_btn') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </section>

    {{-- Bottom CTA --}}
    <section class="relative overflow-hidden border-t border-zinc-100 dark:border-zinc-800/50">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50/30 to-indigo-50/30 dark:from-blue-950/10 dark:to-indigo-950/10"></div>
        <div class="relative mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 lg:px-8">
            <flux:heading size="lg">{{ $this->t('bottom_cta_heading') }}</flux:heading>
            <flux:text class="mt-4 !text-lg">{{ $this->t('bottom_cta_desc') }}</flux:text>
            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <flux:button variant="primary" href="{{ route('register') }}" wire:navigate class="!h-12 !text-base !rounded-xl !px-8 !font-semibold shadow-lg shadow-blue-500/25">
                    {{ $this->t('bottom_cta_btn') }}
                    <flux:icon.arrow-right class="size-4 ml-1" />
                </flux:button>
                <flux:button variant="outline" href="{{ localizedRoute('pricing', ['locale' => app()->getLocale()]) }}" wire:navigate class="!h-12 !text-base !rounded-xl">
                    {{ $this->t('bottom_cta_pricing') }}
                </flux:button>
            </div>
        </div>
    </section>

</flux:main>
