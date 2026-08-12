@php
    $locale = app()->getLocale();
    $title = $page->title ?? __('About');
    $texts = [
            'hero_badge' => [
                'zh-CN' => 'SOHO 全栈开发',
                'en' => 'SOHO Full-Stack Development',
            ],
            'hero_heading' => [
                'zh-CN' => 'PHP / Go / Java 现代化开发',
                'en' => 'Modern PHP / Go / Java Development',
            ],
            'hero_desc' => [
                'zh-CN' => '多年后端开发经验，掌握多语言技术栈，代码规范、结构清晰、易维护。只接靠谱项目，诚信交付。',
                'en' => 'Years of backend development experience, proficient in multiple tech stacks. Clean code, clear structure, easy to maintain. Only take on reliable projects, delivered with integrity.',
            ],
            'services_badge' => [
                'zh-CN' => '可接范围',
                'en' => 'Services Offered',
            ],
            'services_heading' => [
                'zh-CN' => '我能帮你做什么',
                'en' => 'What I Can Do for You',
            ],
            'service_new_title' => [
                'zh-CN' => '新项目开发',
                'en' => 'New Project Development',
            ],
            'service_new_desc' => [
                'zh-CN' => '网站、后台管理系统、API 接口、业务系统，从零到交付',
                'en' => 'Websites, admin panels, API services, business systems — from zero to delivery',
            ],
            'service_maintenance_title' => [
                'zh-CN' => '二次开发 / 维护',
                'en' => 'Secondary Development / Maintenance',
            ],
            'service_maintenance_desc' => [
                'zh-CN' => '老项目功能新增、Bug 修复、逻辑梳理，接手遗留代码',
                'en' => 'Feature additions, bug fixes, logic refactoring for legacy projects, handling inherited code',
            ],
            'service_upgrade_title' => [
                'zh-CN' => '版本 / 框架升级',
                'en' => 'Version / Framework Upgrade',
            ],
            'service_upgrade_desc' => [
                'zh-CN' => 'PHP 版本升级、框架升级迁移、代码重构，平稳过渡',
                'en' => 'PHP version upgrades, framework migration, code refactoring — smooth transitions',
            ],
            'service_performance_title' => [
                'zh-CN' => '性能优化 / 安全加固',
                'en' => 'Performance Optimization / Security Hardening',
            ],
            'service_performance_desc' => [
                'zh-CN' => 'SQL 慢查询优化、缓存策略、安全漏洞修复、代码规范提升',
                'en' => 'Slow SQL query optimization, caching strategies, security vulnerability fixes, code quality improvement',
            ],
            'service_integration_title' => [
                'zh-CN' => '接口 / 集成开发',
                'en' => 'API / Integration Development',
            ],
            'service_integration_desc' => [
                'zh-CN' => '第三方 API 对接、支付集成、短信/邮件服务、数据同步',
                'en' => 'Third-party API integration, payment integration, SMS/email services, data synchronization',
            ],
            'service_consulting_title' => [
                'zh-CN' => '技术咨询',
                'en' => 'Technical Consulting',
            ],
            'service_consulting_desc' => [
                'zh-CN' => '架构评审、技术选型建议、代码 Review，少走弯路',
                'en' => 'Architecture review, tech stack recommendations, code review — avoid detours',
            ],
            'tech_badge' => [
                'zh-CN' => '技术栈',
                'en' => 'Tech Stack',
            ],
            'tech_heading' => [
                'zh-CN' => '按你指定，灵活适配',
                'en' => 'Flexible Adaptation to Your Needs',
            ],
            'tech_desc' => [
                'zh-CN' => '以下是我的常用技术栈，也可以按你的需求适配其他技术。',
                'en' => 'Below is my commonly used tech stack. I can also adapt to other technologies per your requirements.',
            ],
            'tech_frameworks_label' => [
                'zh-CN' => '框架',
                'en' => 'Frameworks',
            ],
            'tech_lang_label' => [
                'zh-CN' => '语言 / 环境',
                'en' => 'Languages / Environment',
            ],
            'tech_direction_label' => [
                'zh-CN' => '方向',
                'en' => 'Specialization',
            ],
            'tech_other_label' => [
                'zh-CN' => '其他',
                'en' => 'Other',
            ],
            'workflow_badge' => [
                'zh-CN' => '服务流程',
                'en' => 'Workflow',
            ],
            'workflow_heading' => [
                'zh-CN' => '从需求到交付，清晰透明',
                'en' => 'From Requirements to Delivery, Clear and Transparent',
            ],
            'workflow_step_1_title' => [
                'zh-CN' => '需求沟通',
                'en' => 'Requirement Discussion',
            ],
            'workflow_step_1_desc' => [
                'zh-CN' => '发我需求文档或截图，详细沟通确认细节',
                'en' => 'Send me your requirements doc or screenshots, we\'ll discuss and confirm details',
            ],
            'workflow_step_2_title' => [
                'zh-CN' => '方案确认',
                'en' => 'Solution Confirmation',
            ],
            'workflow_step_2_desc' => [
                'zh-CN' => '出技术方案、工时评估、报价，达成一致后启动',
                'en' => 'Technical proposal, effort estimation, quotation — start after mutual agreement',
            ],
            'workflow_step_3_title' => [
                'zh-CN' => '开发实现',
                'en' => 'Development',
            ],
            'workflow_step_3_desc' => [
                'zh-CN' => '按节点推进，定期同步进度，保障代码质量',
                'en' => 'Progress on milestones, regular sync-ups, ensuring code quality',
            ],
            'workflow_step_4_title' => [
                'zh-CN' => '测试交付',
                'en' => 'Testing & Delivery',
            ],
            'workflow_step_4_desc' => [
                'zh-CN' => '自测通过后交付验收，配合修改直到满意',
                'en' => 'Self-tested before delivery, cooperate on revisions until you\'re satisfied',
            ],
            'workflow_step_5_title' => [
                'zh-CN' => '售后答疑',
                'en' => 'After-Sales Support',
            ],
            'workflow_step_5_desc' => [
                'zh-CN' => '交付后提供技术支持，长期合作无忧',
                'en' => 'Technical support after delivery, worry-free long-term cooperation',
            ],
            'cta_heading' => [
                'zh-CN' => '有 PHP / Go / Java 项目要开发？',
                'en' => 'Have a PHP / Go / Java Project to Develop?',
            ],
            'cta_desc' => [
                'zh-CN' => '直接发需求文档或截图，免费初步评估。<br class="hidden sm:block" />诚信接单，不接违法违规项目。',
                'en' => 'Send your requirements document or screenshots directly for a free initial assessment.<br class="hidden sm:block" />Honest service, no illegal or non-compliant projects.',
            ],
            'contact_label' => [
                'zh-CN' => '联系我',
                'en' => 'Contact Me',
            ],
        ];

    $t = fn (string $key): string => $texts[$key][$locale] ?? $key;
@endphp

<x-layouts::landing.default :title="$page->title ?? null">

<flux:main>

    {{-- Hero --}}
    <section class="relative overflow-hidden pt-28 pb-20">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] rounded-full bg-gradient-to-b from-blue-100/60 via-indigo-50/30 to-transparent blur-3xl dark:from-blue-900/20 dark:via-indigo-900/10"></div>
        </div>
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <flux:badge color="blue" class="!rounded-full !px-3 !py-1 !text-xs">{{ $t('hero_badge') }}</flux:badge>
            <flux:heading size="xl" class="mt-6">{{ $t('hero_heading') }}</flux:heading>
            <flux:text class="mx-auto mt-6 max-w-2xl !text-lg !leading-relaxed">
                {{ $t('hero_desc') }}
            </flux:text>
        </div>
    </section>

    {{-- Services --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="green" class="!rounded-full !px-3 !py-1 !text-xs">{{ $t('services_badge') }}</flux:badge>
                <flux:heading size="lg" class="mt-6">{{ $t('services_heading') }}</flux:heading>
            </div>
            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg dark:hover:border-blue-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/50 dark:to-blue-800/50 text-2xl">🆕</div>
                    <flux:heading class="mt-5 !text-base">{{ $t('service_new_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $t('service_new_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-lg dark:hover:border-amber-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 dark:from-amber-900/50 dark:to-amber-800/50 text-2xl">🔧</div>
                    <flux:heading class="mt-5 !text-base">{{ $t('service_maintenance_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $t('service_maintenance_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-purple-200 hover:shadow-lg dark:hover:border-purple-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/50 dark:to-purple-800/50 text-2xl">⬆️</div>
                    <flux:heading class="mt-5 !text-base">{{ $t('service_upgrade_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $t('service_upgrade_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-green-200 hover:shadow-lg dark:hover:border-green-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/50 dark:to-green-800/50 text-2xl">⚡</div>
                    <flux:heading class="mt-5 !text-base">{{ $t('service_performance_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $t('service_performance_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-rose-200 hover:shadow-lg dark:hover:border-rose-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-100 to-rose-200 dark:from-rose-900/50 dark:to-rose-800/50 text-2xl">🔗</div>
                    <flux:heading class="mt-5 !text-base">{{ $t('service_integration_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $t('service_integration_desc') }}</flux:text>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-teal-200 hover:shadow-lg dark:hover:border-teal-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-100 to-teal-200 dark:from-teal-900/50 dark:to-teal-800/50 text-2xl">🎯</div>
                    <flux:heading class="mt-5 !text-base">{{ $t('service_consulting_title') }}</flux:heading>
                    <flux:text class="mt-2">{{ $t('service_consulting_desc') }}</flux:text>
                </flux:card>
            </div>
        </div>
    </section>

    {{-- Tech Stack --}}
    <section class="py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="purple" class="!rounded-full !px-3 !py-1 !text-xs">{{ $t('tech_badge') }}</flux:badge>
                <flux:heading size="lg" class="mt-6">{{ $t('tech_heading') }}</flux:heading>
                <flux:text class="mt-4">{{ $t('tech_desc') }}</flux:text>
            </div>
            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-lg dark:hover:border-red-800">
                    <flux:heading class="!text-base !font-semibold">{{ $t('tech_frameworks_label') }}</flux:heading>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <flux:badge color="red" class="!rounded-lg">Laravel</flux:badge>
                        <flux:badge color="blue" class="!rounded-lg">ThinkPHP</flux:badge>
                        <flux:badge color="green" class="!rounded-lg">Yii</flux:badge>
                        <flux:badge color="amber" class="!rounded-lg">CodeIgniter</flux:badge>
                        <flux:badge color="zinc" class="!rounded-lg">Hyperf</flux:badge>
                        <flux:badge color="sky" class="!rounded-lg">Gin</flux:badge>
                        <flux:badge color="cyan" class="!rounded-lg">Fiber</flux:badge>
                        <flux:badge color="orange" class="!rounded-lg">Spring Boot</flux:badge>
                    </div>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-sky-200 hover:shadow-lg dark:hover:border-sky-800">
                    <flux:heading class="!text-base !font-semibold">{{ $t('tech_lang_label') }}</flux:heading>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <flux:badge color="sky" class="!rounded-lg">PHP 7.x</flux:badge>
                        <flux:badge color="sky" class="!rounded-lg">PHP 8.x</flux:badge>
                        <flux:badge color="blue" class="!rounded-lg">Go</flux:badge>
                        <flux:badge color="red" class="!rounded-lg">Java</flux:badge>
                        <flux:badge color="indigo" class="!rounded-lg">MySQL</flux:badge>
                        <flux:badge color="rose" class="!rounded-lg">Redis</flux:badge>
                        <flux:badge color="green" class="!rounded-lg">Nginx</flux:badge>
                    </div>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg dark:hover:border-indigo-800">
                    <flux:heading class="!text-base !font-semibold">{{ $t('tech_direction_label') }}</flux:heading>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <flux:badge color="indigo" class="!rounded-lg">纯后端</flux:badge>
                        <flux:badge color="purple" class="!rounded-lg">前后端分离</flux:badge>
                        <flux:badge color="pink" class="!rounded-lg">API 服务</flux:badge>
                        <flux:badge color="orange" class="!rounded-lg">全栈</flux:badge>
                    </div>
                </flux:card>
                <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg dark:hover:border-emerald-800">
                    <flux:heading class="!text-base !font-semibold">{{ $t('tech_other_label') }}</flux:heading>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <flux:badge color="emerald" class="!rounded-lg">Docker</flux:badge>
                        <flux:badge color="teal" class="!rounded-lg">Git</flux:badge>
                        <flux:badge color="cyan" class="!rounded-lg">CI/CD</flux:badge>
                        <flux:badge color="zinc" class="!rounded-lg">Linux</flux:badge>
                    </div>
                </flux:card>
            </div>
        </div>
    </section>

    {{-- Workflow --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="amber" class="!rounded-full !px-3 !py-1 !text-xs">{{ $t('workflow_badge') }}</flux:badge>
                <flux:heading size="lg" class="mt-6">{{ $t('workflow_heading') }}</flux:heading>
            </div>
            <div class="mt-16 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @php
                    $steps = [
                        ['emoji' => '📋', 'title_key' => 'workflow_step_1_title', 'desc_key' => 'workflow_step_1_desc'],
                        ['emoji' => '✍️', 'title_key' => 'workflow_step_2_title', 'desc_key' => 'workflow_step_2_desc'],
                        ['emoji' => '💻', 'title_key' => 'workflow_step_3_title', 'desc_key' => 'workflow_step_3_desc'],
                        ['emoji' => '🧪', 'title_key' => 'workflow_step_4_title', 'desc_key' => 'workflow_step_4_desc'],
                        ['emoji' => '💬', 'title_key' => 'workflow_step_5_title', 'desc_key' => 'workflow_step_5_desc'],
                    ];
                @endphp
                @foreach ($steps as $step)
                    <flux:card class="group text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                        <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-zinc-100 to-zinc-200 text-2xl dark:from-zinc-800 dark:to-zinc-700">{{ $step['emoji'] }}</div>
                        <flux:heading class="mt-4 !text-base">{{ $t($step['title_key']) }}</flux:heading>
                        <flux:text class="mt-2 !text-sm">{{ $t($step['desc_key']) }}</flux:text>
                    </flux:card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Bottom CTA --}}
    <section class="relative overflow-hidden border-t border-zinc-100 dark:border-zinc-800/50">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50/30 to-indigo-50/30 dark:from-blue-950/10 dark:to-indigo-950/10"></div>
        <div class="relative mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 lg:px-8">
            <flux:heading size="lg">{{ $t('cta_heading') }}</flux:heading>
            <flux:text class="mt-4 !text-lg">
                {!! $t('cta_desc') !!}
            </flux:text>
            <div class="mt-10">
                <flux:heading size="base" class="!font-semibold">{{ $t('contact_label') }}</flux:heading>
                <div class="mt-4 inline-flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-8 py-4 text-lg font-semibold text-green-700 shadow-sm dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.952-7.062-6.122zm-2.18 2.769c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982z"/></svg>
                    <span class="whitespace-nowrap">微信：runphp</span>
                </div>
            </div>
        </div>
    </section>

</flux:main>
</x-layouts::landing.default>
