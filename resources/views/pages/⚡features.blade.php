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
        $page = Page::findBySlug('features');
        view()->share('title', $page?->title ?? __('Features'));
    }

    public function t(string $key): string
    {
        return $this->texts()[$key][$this->locale] ?? $key;
    }

    private function texts(): array
    {
        return [
            'hero_badge' => [
                'zh-CN' => '应用市场',
                'en' => 'App Marketplace',
            ],
            'hero_heading' => [
                'zh-CN' => '像搭积木一样构建你的系统',
                'en' => 'Build Your System Like Building Blocks',
            ],
            'hero_desc' => [
                'zh-CN' => '一个开放的应用模块市场：按需挑选模块自由组合，付费模块收益归模块开发者。可定制新模块或二次开发现有模块，开源通用模块还能享受更优价格。',
                'en' => 'An open app module marketplace: pick modules on demand and combine freely, paid module revenue goes to developers. Customize new modules or enhance existing ones, and open-sourced general modules enjoy better pricing.',
            ],
            'how_badge' => [
                'zh-CN' => '运作模式',
                'en' => 'How It Works',
            ],
            'how_heading' => [
                'zh-CN' => '模块市场怎么玩',
                'en' => 'How the Module Marketplace Works',
            ],
            'step_browse_title' => [
                'zh-CN' => '浏览模块',
                'en' => 'Browse Modules',
            ],
            'step_browse_desc' => [
                'zh-CN' => '在市场中挑选你需要的功能模块',
                'en' => 'Browse and pick the functional modules you need',
            ],
            'step_combine_title' => [
                'zh-CN' => '自由组合',
                'en' => 'Free Combination',
            ],
            'step_combine_desc' => [
                'zh-CN' => '按需搭配，只装你真正要用的功能',
                'en' => 'Mix and match on demand, only install what you really need',
            ],
            'step_custom_title' => [
                'zh-CN' => '定制开发',
                'en' => 'Custom Development',
            ],
            'step_custom_desc' => [
                'zh-CN' => '没有满意的？定制新模块或改造现有模块',
                'en' => 'Not satisfied? Customize new modules or enhance existing ones',
            ],
            'step_opensource_title' => [
                'zh-CN' => '开源回流',
                'en' => 'Open Source Contribution',
            ],
            'step_opensource_desc' => [
                'zh-CN' => '定制的通用模块开源，享更优价格',
                'en' => 'Open-source customized general modules for better pricing',
            ],
            'step_prosper_title' => [
                'zh-CN' => '持续繁荣',
                'en' => 'Continuous Prosperity',
            ],
            'step_prosper_desc' => [
                'zh-CN' => '模块越丰富，后续开发成本越低',
                'en' => 'The richer the modules, the lower future development costs',
            ],
            'roles_badge' => [
                'zh-CN' => '生态共赢',
                'en' => 'Win-Win Ecosystem',
            ],
            'roles_heading' => [
                'zh-CN' => '三方受益，正向循环',
                'en' => 'Three-Way Benefit, Positive Cycle',
            ],
            'role_client_title' => [
                'zh-CN' => '客户方',
                'en' => 'Client',
            ],
            'role_client_desc' => [
                'zh-CN' => '按需选取模块，用多少装多少。需要个性化功能？可定制开发，通用模块还能通过开源回流获得费用优惠。前期投入，后期受益。',
                'en' => 'Choose modules on demand, install only what you use. Need customization? Get custom development, and general modules enjoy cost savings through open-source contribution. Invest early, benefit later.',
            ],
            'role_developer_title' => [
                'zh-CN' => '模块开发者',
                'en' => 'Module Developer',
            ],
            'role_developer_desc' => [
                'zh-CN' => '开发通用模块上架市场，每次被客户选用即获得收益。一次开发，持续收入。',
                'en' => 'Develop general modules and publish them on the marketplace, earn revenue every time a client uses them. Develop once, earn continuously.',
            ],
            'role_ecosystem_title' => [
                'zh-CN' => '市场生态',
                'en' => 'Marketplace Ecosystem',
            ],
            'role_ecosystem_desc' => [
                'zh-CN' => '每个开源回流的模块都在壮大公共资产池。模块越丰富，下一个项目的搭建成本就越低，形成正向循环。',
                'en' => 'Each open-sourced module grows the public asset pool. The richer the modules, the lower the cost for the next project, forming a positive cycle.',
            ],
            'opensource_badge' => [
                'zh-CN' => '开源激励机制',
                'en' => 'Open Source Incentive',
            ],
            'opensource_heading' => [
                'zh-CN' => '你开源，我降价',
                'en' => 'You Open Source, I Lower the Price',
            ],
            'opensource_desc' => [
                'zh-CN' => '如果你定制的模块<strong>通用性强</strong>，愿意将其开源贡献到市场中，我会在开发费用上给予<strong>更优的价格</strong>。',
                'en' => 'If your customized module is <strong>highly general-purpose</strong> and you\'re willing to open-source it to the marketplace, I\'ll offer <strong>better pricing</strong> on development costs.',
            ],
            'opensource_why_title' => [
                'zh-CN' => '为什么值得开源?',
                'en' => 'Why Open Source?',
            ],
            'opensource_reason_1' => [
                'zh-CN' => '定制费更优惠，边际成本更低',
                'en' => 'Lower customization fees, reduced marginal costs',
            ],
            'opensource_reason_2' => [
                'zh-CN' => '模块经社区打磨后更稳定、更安全',
                'en' => 'Modules become more stable and secure after community refinement',
            ],
            'opensource_reason_3' => [
                'zh-CN' => '后续其他人二次开发时，基础功能不用重复造轮子',
                'en' => 'Others won\'t need to reinvent basic features when doing secondary development',
            ],
            'opensource_reason_4' => [
                'zh-CN' => '整个生态的开发成本持续走低',
                'en' => 'Overall ecosystem development costs continue to decrease',
            ],
            'cta_heading' => [
                'zh-CN' => '有想法？聊聊看',
                'en' => 'Got an Idea? Let\'s Talk',
            ],
            'cta_desc' => [
                'zh-CN' => '想搭建系统还是贡献模块？大胆说出你的想法。<br class="hidden sm:block" />免费评估，认真对待每一个需求。',
                'en' => 'Want to build a system or contribute modules? Share your ideas boldly.<br class="hidden sm:block" />Free evaluation, every request taken seriously.',
            ],
            'contact_label' => [
                'zh-CN' => '联系我',
                'en' => 'Contact Me',
            ],
        ];
    }
}; ?>

<flux:main>

    {{-- Hero --}}
    <section class="relative overflow-hidden pt-28 pb-20">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] rounded-full bg-gradient-to-b from-violet-100/60 via-fuchsia-50/30 to-transparent blur-3xl dark:from-violet-900/20 dark:via-fuchsia-900/10"></div>
        </div>
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <flux:badge color="violet" class="!rounded-full !px-3 !py-1 !text-xs">{{ $this->t('hero_badge') }}</flux:badge>
            <flux:heading size="xl" class="mt-6">{{ $this->t('hero_heading') }}</flux:heading>
            <flux:text class="mx-auto mt-6 max-w-2xl !text-lg !leading-relaxed">
                {{ $this->t('hero_desc') }}
            </flux:text>
        </div>
    </section>

    {{-- How It Works --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="indigo" class="!rounded-full !px-3 !py-1 !text-xs">{{ $this->t('how_badge') }}</flux:badge>
                <flux:heading size="lg" class="mt-6">{{ $this->t('how_heading') }}</flux:heading>
            </div>
            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
                @php
                $steps = [
                    ['emoji' => '🧩', 'title_key' => 'step_browse_title', 'desc_key' => 'step_browse_desc', 'color' => 'blue'],
                    ['emoji' => '🛠️', 'title_key' => 'step_combine_title', 'desc_key' => 'step_combine_desc', 'color' => 'violet'],
                    ['emoji' => '✨', 'title_key' => 'step_custom_title', 'desc_key' => 'step_custom_desc', 'color' => 'amber'],
                    ['emoji' => '🌍', 'title_key' => 'step_opensource_title', 'desc_key' => 'step_opensource_desc', 'color' => 'emerald'],
                    ['emoji' => '📈', 'title_key' => 'step_prosper_title', 'desc_key' => 'step_prosper_desc', 'color' => 'rose'],
                ];
                @endphp
                @foreach ($steps as $step)
                    <flux:card class="group text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                        <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-{{ $step['color'] }}-100 to-{{ $step['color'] }}-200 dark:from-{{ $step['color'] }}-900/50 dark:to-{{ $step['color'] }}-800/50 text-2xl">{{ $step['emoji'] }}</div>
                        <flux:heading class="mt-5 !text-base">{{ $this->t($step['title_key']) }}</flux:heading>
                        <flux:text class="mt-2 !text-sm">{{ $this->t($step['desc_key']) }}</flux:text>
                    </flux:card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Three Roles --}}
    <section class="py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <flux:badge color="emerald" class="!rounded-full !px-3 !py-1 !text-xs">{{ $this->t('roles_badge') }}</flux:badge>
                <flux:heading size="lg" class="mt-6">{{ $this->t('roles_heading') }}</flux:heading>
            </div>
            <div class="mt-16 grid gap-6 lg:grid-cols-3">
                {{-- Client --}}
                <flux:card class="transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg dark:hover:border-blue-700">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50 text-xl">🛒</div>
                        <flux:heading class="!text-base">{{ $this->t('role_client_title') }}</flux:heading>
                    </div>
                    <flux:text class="mt-4 !text-sm">
                        {{ $this->t('role_client_desc') }}
                    </flux:text>
                </flux:card>

                {{-- Developer --}}
                <flux:card class="transition-all duration-300 hover:-translate-y-1 hover:border-purple-300 hover:shadow-lg dark:hover:border-purple-700">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/50 text-xl">💎</div>
                        <flux:heading class="!text-base">{{ $this->t('role_developer_title') }}</flux:heading>
                    </div>
                    <flux:text class="mt-4 !text-sm">
                        {{ $this->t('role_developer_desc') }}
                    </flux:text>
                </flux:card>

                {{-- Ecosystem --}}
                <flux:card class="transition-all duration-300 hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg dark:hover:border-amber-700">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/50 text-xl">🌱</div>
                        <flux:heading class="!text-base">{{ $this->t('role_ecosystem_title') }}</flux:heading>
                    </div>
                    <flux:text class="mt-4 !text-sm">
                        {{ $this->t('role_ecosystem_desc') }}
                    </flux:text>
                </flux:card>
            </div>
        </div>
    </section>

    {{-- Open Source Incentive --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <flux:badge color="green" class="!rounded-full !px-3 !py-1 !text-xs">{{ $this->t('opensource_badge') }}</flux:badge>
            <flux:heading size="lg" class="mt-6">{{ $this->t('opensource_heading') }}</flux:heading>
            <flux:text class="mt-6 !text-lg !leading-relaxed">
                {!! $this->t('opensource_desc') !!}
            </flux:text>
            <div class="mt-10 flex justify-center">
                <div class="max-w-xl rounded-2xl border-2 border-dashed border-green-300 bg-green-50/50 px-6 py-5 text-left dark:border-green-800 dark:bg-green-950/30">
                    <p class="text-sm font-semibold text-green-800 dark:text-green-200">{{ $this->t('opensource_why_title') }}</p>
                    <ul class="mt-3 space-y-2 text-sm text-green-700 dark:text-green-300">
                        <li>•  {{ $this->t('opensource_reason_1') }}</li>
                        <li>•  {{ $this->t('opensource_reason_2') }}</li>
                        <li>•  {{ $this->t('opensource_reason_3') }}</li>
                        <li>•  {{ $this->t('opensource_reason_4') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative overflow-hidden border-t border-zinc-100 dark:border-zinc-800/50">
        <div class="absolute inset-0 bg-gradient-to-b from-violet-50/30 to-fuchsia-50/30 dark:from-violet-950/10 dark:to-fuchsia-950/10"></div>
        <div class="relative mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 lg:px-8">
            <flux:heading size="lg">{{ $this->t('cta_heading') }}</flux:heading>
            <flux:text class="mt-4 !text-lg">
                {!! $this->t('cta_desc') !!}
            </flux:text>
            <div class="mt-10">
                <flux:heading size="base" class="!font-semibold">{{ $this->t('contact_label') }}</flux:heading>
                <div class="mt-4 inline-flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-8 py-4 text-lg font-semibold text-green-700 shadow-sm dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.952-7.062-6.122zm-2.18 2.769c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982z"/></svg>
                    <span class="whitespace-nowrap">微信：runphp</span>
                </div>
            </div>
        </div>
    </section>

</flux:main>
