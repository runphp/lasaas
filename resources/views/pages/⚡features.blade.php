<?php

use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::landing')] class extends Component
{
    public function mount(): void
    {
        $page = Page::findBySlug('features');
        view()->share('title', $page?->title ?? __('Features'));
    }
}; ?>

<flux:main>

    {{-- Hero --}}
    <section class="relative overflow-hidden pt-28 pb-20">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] rounded-full bg-gradient-to-b from-violet-100/60 via-fuchsia-50/30 to-transparent blur-3xl dark:from-violet-900/20 dark:via-fuchsia-900/10"></div>
        </div>
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <flux:badge color="violet" class="!rounded-full !px-3 !py-1 !text-xs">定制开发</flux:badge>
            <flux:heading size="xl" class="mt-6">你的需求，就是功能列表</flux:heading>
            <flux:text class="mx-auto mt-6 max-w-2xl !text-lg !leading-relaxed">
                这里不是一个功能固定的 SaaS 产品，而是根据你的业务场景<strong>按需开发</strong>。<br class="hidden sm:block" />
                没有多余模块，不需要的功能不会出现，你需要的功能一个不少。
            </flux:text>
        </div>
    </section>

    {{-- What can be built --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="indigo" class="!rounded-full !px-3 !py-1 !text-xs">可快速实现</flux:badge>
                <flux:heading size="lg" class="mt-6">常见业务系统，都能做</flux:heading>
                <flux:text class="mt-4">以下只是冰山一角，告诉我你的场景，我来实现。</flux:text>
            </div>
            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $systems = [
                        ['emoji' => '🛒', 'title' => '电商 / 商城系统', 'desc' => '商品管理、订单流转、支付接入、库存管理', 'color' => 'orange'],
                        ['emoji' => '📊', 'title' => '数据管理后台', 'desc' => 'CRUD 面板、权限控制、数据导入导出、图表看板', 'color' => 'blue'],
                        ['emoji' => '🔗', 'title' => 'API 服务 / 数据中台', 'desc' => 'RESTful / GraphQL 接口、多端数据服务、接口文档自动生成', 'color' => 'sky'],
                        ['emoji' => '👥', 'title' => '多租户 SaaS 平台', 'desc' => '租户隔离、自定义域名、按量计费、白标定制', 'color' => 'purple'],
                        ['emoji' => '📱', 'title' => '小程序 / App 后端', 'desc' => '用户认证、推送通知、内容管理、支付回调', 'color' => 'green'],
                        ['emoji' => '🤖', 'title' => '自动化 / 工作流系统', 'desc' => '审批流配置、定时任务、规则引擎、消息通知', 'color' => 'amber'],
                        ['emoji' => '📝', 'title' => 'CMS / 内容管理系统', 'desc' => '多语言、媒体管理、SEO 友好、Markdown 支持', 'color' => 'red'],
                        ['emoji' => '📈', 'title' => '数据采集 / 报表系统', 'desc' => '爬虫抓取、ETL 清洗、BI 报表、数据可视化', 'color' => 'teal'],
                        ['emoji' => '💳', 'title' => '支付 / 账务系统', 'desc' => '分账结算、对账流水、发票管理、多通道聚合', 'color' => 'pink'],
                    ];
                @endphp
                @foreach ($systems as $s)
                    <flux:card class="group transition-all duration-300 hover:-translate-y-1 hover:border-{{ $s['color'] }}-200 hover:shadow-lg dark:hover:border-{{ $s['color'] }}-800">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-{{ $s['color'] }}-100 to-{{ $s['color'] }}-200 dark:from-{{ $s['color'] }}-900/50 dark:to-{{ $s['color'] }}-800/50 text-2xl">{{ $s['emoji'] }}</div>
                        <flux:heading class="mt-5 !text-base">{{ $s['title'] }}</flux:heading>
                        <flux:text class="mt-2">{{ $s['desc'] }}</flux:text>
                    </flux:card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Advantage --}}
    <section class="py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="emerald" class="!rounded-full !px-3 !py-1 !text-xs">开发优势</flux:badge>
                <flux:heading size="lg" class="mt-6">不是买功能，是按需开发</flux:heading>
                <flux:text class="mt-4">与传统 SaaS 产品不同：不用为不需要的功能买单，也不受限于预设的功能边界。</flux:text>
            </div>
            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <flux:card class="text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/50 dark:to-emerald-800/50 text-2xl">🎯</div>
                    <flux:heading class="mt-5 !text-base">精准匹配需求</flux:heading>
                    <flux:text class="mt-2 !text-sm">没有多余功能堆砌，每一个模块都为你量身定制</flux:text>
                </flux:card>
                <flux:card class="text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/50 dark:to-blue-800/50 text-2xl">⚡</div>
                    <flux:heading class="mt-5 !text-base">快速交付</flux:heading>
                    <flux:text class="mt-2 !text-sm">成熟的技术底座，从零到可用比你想的更快</flux:text>
                </flux:card>
                <flux:card class="text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-100 to-violet-200 dark:from-violet-900/50 dark:to-violet-800/50 text-2xl">🧩</div>
                    <flux:heading class="mt-5 !text-base">灵活扩展</flux:heading>
                    <flux:text class="mt-2 !text-sm">随着业务发展，随时增加新模块，持续迭代</flux:text>
                </flux:card>
                <flux:card class="text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-100 to-rose-200 dark:from-rose-900/50 dark:to-rose-800/50 text-2xl">🔐</div>
                    <flux:heading class="mt-5 !text-base">100% 掌控</flux:heading>
                    <flux:text class="mt-2 !text-sm">代码交付给你，数据完全自主，不受平台限制</flux:text>
                </flux:card>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative overflow-hidden border-t border-zinc-100 dark:border-zinc-800/50">
        <div class="absolute inset-0 bg-gradient-to-b from-violet-50/30 to-fuchsia-50/30 dark:from-violet-950/10 dark:to-fuchsia-950/10"></div>
        <div class="relative mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 lg:px-8">
            <flux:heading size="lg">有想法？聊聊看</flux:heading>
            <flux:text class="mt-4 !text-lg">
                大胆说出你的业务场景，免费评估可行性和工时。<br class="hidden sm:block" />
                再奇葩的需求，也值得认真讨论。
            </flux:text>
            <div class="mt-10">
                <flux:heading size="base" class="!font-semibold">联系我</flux:heading>
                <div class="mt-4 inline-flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-8 py-4 text-lg font-semibold text-green-700 shadow-sm dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.952-7.062-6.122zm-2.18 2.769c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982z"/></svg>
                    <span class="whitespace-nowrap">微信：runphp</span>
                </div>
            </div>
        </div>
    </section>

</flux:main>
