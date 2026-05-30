<?php

use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::landing')] class extends Component
{
    public function mount(): void
    {
        $page = Page::findBySlug('home');
        view()->share('title', $page?->title ?? __('Lasaas - Laravel Multi-Tenant SaaS Platform'));
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
            <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-blue-200/60 bg-blue-50/80 px-4 py-1.5 text-sm font-medium text-blue-700 backdrop-blur-sm dark:border-blue-800/60 dark:bg-blue-950/80 dark:text-blue-300">
                <span class="relative flex size-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex size-2 rounded-full bg-green-500"></span>
                </span>
                永久免费开源 · MIT 协议
            </div>

            {{-- Main heading --}}
            <h1 class="text-5xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-6xl lg:text-7xl">
                一套代码
                <br class="sm:hidden" />
                <span class="relative inline-block">
                    <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">无限可能</span>
                    <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 200 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 4 Q50 0 100 4 Q150 8 200 4" stroke="currentColor" stroke-width="2" class="text-indigo-300/60 dark:text-indigo-600/40" stroke-linecap="round"/>
                    </svg>
                </span>
            </h1>

            <p class="mx-auto mt-8 max-w-3xl text-lg leading-relaxed text-zinc-500 dark:text-zinc-400">
                基于 Laravel 生态的现代化多租户 SaaS 平台。独立数据库隔离、自定义域名、团队协作、RBAC 权限体系——从站群系统到 AI 知识库，开箱即用，极速交付。
            </p>

            {{-- CTA Buttons --}}
            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <flux:button variant="primary" href="{{ route('register') }}" wire:navigate class="!h-12 !text-base !rounded-xl !px-8 !font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-shadow">
                    免费开始使用
                    <flux:icon.arrow-right class="size-4 ml-1" />
                </flux:button>
                <flux:button variant="outline" href="{{ localizedRoute('pricing', ['locale' => app()->getLocale()]) }}" wire:navigate class="!h-12 !text-base !rounded-xl">
                    查看定价
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
                <div class="inline-flex items-center gap-2 rounded-full border border-blue-200/60 bg-blue-50/60 px-3 py-1 text-xs font-semibold text-blue-700 dark:border-blue-800/60 dark:bg-blue-950/60 dark:text-blue-300">
                    核心架构
                </div>
                <h2 class="mt-6 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">多租户架构，数据物理隔离</h2>
                <p class="mt-4 text-base text-zinc-500 dark:text-zinc-400">每个租户拥有独立数据库和专属域名，从底层保障数据安全与隐私合规。</p>
            </div>
            <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-500/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-blue-700">
                    <div class="absolute top-0 right-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-blue-100/50 opacity-0 blur-2xl transition-opacity group-hover:opacity-100 dark:bg-blue-900/30"></div>
                    <div class="relative flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/20">
                        <svg class="h-7 w-7 text-zinc-900 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.737 5.1a3.375 3.375 0 0 1 2.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 0 1 .9 2.7m0 0a3 3 0 0 1-3 3m0 3h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Zm-3 6h.008v.008h-.008v-.008Zm0-6h.008v.008h-.008v-.008Z" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-semibold text-zinc-900 dark:text-white">独立数据库</h3>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">每个租户独立数据库，数据物理隔离，满足等保合规要求</p>
                </div>
                <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-green-300 hover:shadow-xl hover:shadow-green-500/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-green-700">
                    <div class="absolute top-0 right-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-green-100/50 opacity-0 blur-2xl transition-opacity group-hover:opacity-100 dark:bg-green-900/30"></div>
                    <div class="relative flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg shadow-green-500/20">
                        <svg class="h-7 w-7 text-zinc-900 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-semibold text-zinc-900 dark:text-white">独立域名</h3>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">每个租户可绑定专属域名，完全白标，客户无感知</p>
                </div>
                <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-purple-300 hover:shadow-xl hover:shadow-purple-500/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-purple-700">
                    <div class="absolute top-0 right-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-purple-100/50 opacity-0 blur-2xl transition-opacity group-hover:opacity-100 dark:bg-purple-900/30"></div>
                    <div class="relative flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 shadow-lg shadow-purple-500/20">
                        <svg class="h-7 w-7 text-zinc-900 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-semibold text-zinc-900 dark:text-white">团队管辖</h3>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">用户可以在多个团队间切换，每个团队独立管理自己的租户</p>
                </div>
                <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-amber-300 hover:shadow-xl hover:shadow-amber-500/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-700">
                    <div class="absolute top-0 right-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-amber-100/50 opacity-0 blur-2xl transition-opacity group-hover:opacity-100 dark:bg-amber-900/30"></div>
                    <div class="relative flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/20">
                        <svg class="h-7 w-7 text-zinc-900 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.401.604-.401.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-semibold text-zinc-900 dark:text-white">模块开关</h3>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">中央管理平台可精细控制每个租户 App 可用的功能模块</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Grid --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-purple-200/60 bg-purple-50/60 px-3 py-1 text-xs font-semibold text-purple-700 dark:border-purple-800/60 dark:bg-purple-950/60 dark:text-purple-300">
                    强大特性
                </div>
                <h2 class="mt-6 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">开箱即用的全栈能力</h2>
                <p class="mt-4 text-base text-zinc-500 dark:text-zinc-400">Filament 一行命令生成 CRUD，Filament Shield 零代码分配权限——从零到上线只需几天。</p>
            </div>
            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 dark:from-amber-900/50 dark:to-amber-800/50">
                        <flux:icon.squares-plus class="size-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Filament 管理面板</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">强大的管理面板，统一管理用户、团队、租户、角色和权限，支持自定义扩展</p>
                </div>
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-pink-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-pink-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-pink-100 to-rose-200 dark:from-pink-900/50 dark:to-rose-800/50">
                        <flux:icon.bolt class="size-6 text-pink-600 dark:text-pink-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Livewire + Flux UI</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">零 JavaScript 实现全栈响应式交互，Flux 组件库开箱即用，开发效率数倍提升</p>
                </div>
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-purple-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-purple-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-100 to-violet-200 dark:from-purple-900/50 dark:to-violet-800/50">
                        <flux:icon.shield-check class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Filament Shield RBAC</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">自动扫描资源、页面、小组件生成细粒度权限，后台点点鼠标即可完成角色分配</p>
                </div>
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-green-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-green-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-green-100 to-emerald-200 dark:from-green-900/50 dark:to-emerald-800/50">
                        <flux:icon.user-group class="size-6 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">团队协作</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">创建团队、邀请成员、分配角色，团队之间数据隔离，权限管理内置</p>
                </div>
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-rose-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-rose-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-100 to-pink-200 dark:from-rose-900/50 dark:to-pink-800/50">
                        <flux:icon.lock-closed class="size-6 text-rose-600 dark:text-rose-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">认证与安全</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">双因素认证、Passkeys 无密码登录、CSRF/XSS/SQL 注入防护，全方位安全保障</p>
                </div>
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-orange-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-orange-100 to-amber-200 dark:from-orange-900/50 dark:to-amber-800/50">
                        <flux:icon.language class="size-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">国际化</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">内置中英文双语支持，轻松扩展更多语言</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Applicable Scenarios --}}
    <section class="py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-green-200/60 bg-green-50/60 px-3 py-1 text-xs font-semibold text-green-700 dark:border-green-800/60 dark:bg-green-950/60 dark:text-green-300">
                    业务场景
                </div>
                <h2 class="mt-6 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">覆盖多种业务场景</h2>
                <p class="mt-4 text-base text-zinc-500 dark:text-zinc-400">多租户 + 独立数据库 + 独立域名的架构，一套代码服务成百上千个独立站点。</p>
            </div>
            <div class="mt-16 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- 站群系统 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-blue-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/50 dark:to-blue-800/50">
                        <flux:icon.globe-alt class="size-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="mt-5 font-semibold text-zinc-900 dark:text-white">站群系统</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">几百个站点，一个 git pull 全部更新</p>
                </div>
                {{-- 企业官网平台 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-indigo-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-200 dark:from-indigo-900/50 dark:to-indigo-800/50">
                        <flux:icon.building-office class="size-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <h3 class="mt-5 font-semibold text-zinc-900 dark:text-white">企业官网平台</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">服务商模式：开发一次，卖给 N 个客户</p>
                </div>
                {{-- 多品牌电商 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 dark:from-amber-900/50 dark:to-amber-800/50">
                        <flux:icon.shopping-cart class="size-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h3 class="mt-5 font-semibold text-zinc-900 dark:text-white">多品牌电商</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">品牌独立运营，数据安全隔离，集中管控</p>
                </div>
                {{-- 多校区管理 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-green-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-green-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/50 dark:to-green-800/50">
                        <flux:icon.academic-cap class="size-6 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mt-5 font-semibold text-zinc-900 dark:text-white">多校区管理</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">校区独立，集团统一，互不干扰</p>
                </div>
                {{-- SaaS 产品创业 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-purple-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-purple-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/50 dark:to-purple-800/50">
                        <flux:icon.cube class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <h3 class="mt-5 font-semibold text-zinc-900 dark:text-white">SaaS 产品创业</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">从 0 到上线只需几天，聚焦业务而非架构</p>
                </div>
                {{-- 物业/园区管理 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-rose-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-rose-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-100 to-rose-200 dark:from-rose-900/50 dark:to-rose-800/50">
                        <flux:icon.home-modern class="size-6 text-rose-600 dark:text-rose-400" />
                    </div>
                    <h3 class="mt-5 font-semibold text-zinc-900 dark:text-white">物业/园区管理</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">一套系统管 N 个小区，物业公司的最爱</p>
                </div>
                {{-- 内容矩阵 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-teal-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-teal-800">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-100 to-teal-200 dark:from-teal-900/50 dark:to-teal-800/50">
                        <flux:icon.newspaper class="size-6 text-teal-600 dark:text-teal-400" />
                    </div>
                    <h3 class="mt-5 font-semibold text-zinc-900 dark:text-white">内容矩阵</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">一套 CMS 撑起整个内容帝国</p>
                </div>
                {{-- 行业软件定制 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-zinc-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800/50 dark:to-zinc-700/50">
                        <flux:icon.cog class="size-6 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <h3 class="mt-5 font-semibold text-zinc-900 dark:text-white">行业软件定制</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">CRM、ERP 等按客户分别部署，代码统一维护</p>
                </div>
            </div>
        </div>
    </section>

    {{-- AI + Multi-Tenant --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-violet-200/60 bg-violet-50/60 px-3 py-1 text-xs font-semibold text-violet-700 dark:border-violet-800/60 dark:bg-violet-950/60 dark:text-violet-300">
                    AI 赋能
                </div>
                <h2 class="mt-6 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">AI + 多租户的化学反应</h2>
                <p class="mt-4 text-base text-zinc-500 dark:text-zinc-400">每个租户的数据是私有的、需要隔离的，而 AI 的能力又是通用的——两者结合，催生高价值场景。</p>
            </div>
            <div class="mt-16 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div class="group flex gap-4 rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-blue-700">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-indigo-200 text-2xl dark:from-blue-900/50 dark:to-indigo-800/50">🧠</div>
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">RAG 知识库平台</h3>
                        <p class="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">企业私有文档问答，知识库绝对隔离</p>
                    </div>
                </div>
                <div class="group flex gap-4 rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-green-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-green-700">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-green-100 to-emerald-200 text-2xl dark:from-green-900/50 dark:to-emerald-800/50">💬</div>
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">AI 客服机器人</h3>
                        <p class="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">每个租户基于自身知识独立训练</p>
                    </div>
                </div>
                <div class="group flex gap-4 rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-purple-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-purple-700">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-100 to-violet-200 text-2xl dark:from-purple-900/50 dark:to-violet-800/50">✍️</div>
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">AI 内容工厂</h3>
                        <p class="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">按品牌生成文案，tone&voice 独立配置</p>
                    </div>
                </div>
                <div class="group flex gap-4 rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-700">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-200 text-2xl dark:from-amber-900/50 dark:to-orange-800/50">📊</div>
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">AI 数据分析</h3>
                        <p class="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">租户上传数据，AI 生成洞察和预测</p>
                    </div>
                </div>
                <div class="group flex gap-4 rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-rose-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-rose-700">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-rose-100 to-pink-200 text-2xl dark:from-rose-900/50 dark:to-pink-800/50">🤖</div>
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">AI Agent 工作流</h3>
                        <p class="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">每个租户编排自己的 Agent 和工具链</p>
                    </div>
                </div>
                <div class="group flex gap-4 rounded-2xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:border-teal-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-teal-700">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-100 to-cyan-200 text-2xl dark:from-teal-900/50 dark:to-cyan-800/50">💰</div>
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">AI 智能记账</h3>
                        <p class="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">财务数据自动分类对账，物理隔离</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Demo + Open Source CTA --}}
    <section class="border-t border-zinc-100 bg-zinc-50/50 py-24 dark:border-zinc-800/50 dark:bg-zinc-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-blue-200/60 bg-blue-50/60 px-3 py-1 text-xs font-semibold text-blue-700 dark:border-blue-800/60 dark:bg-blue-950/60 dark:text-blue-300">
                    快速开始
                </div>
                <h2 class="mt-6 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">立即体验</h2>
                <p class="mt-4 text-base text-zinc-500 dark:text-zinc-400">在线演示环境已就绪，无需部署，即刻体验全功能。</p>
            </div>
            <div class="mt-12 grid gap-8 sm:grid-cols-2">
                <div class="group rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50/50 to-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl dark:border-blue-800/60 dark:from-blue-950/30 dark:to-zinc-900 dark:hover:border-blue-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/25">
                        <svg class="h-6 w-6 text-zinc-900 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900 dark:text-white">在线演示</h3>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">登录即可体验完整功能</p>
                    <div class="mt-6 space-y-3 rounded-xl bg-white/80 p-5 backdrop-blur-sm dark:bg-zinc-800/80">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400 dark:text-zinc-500">演示地址</span>
                            <code class="rounded-lg bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-300">lasaas.doulingvip.com</code>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400 dark:text-zinc-500">管理员账号</span>
                            <code class="rounded-lg bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-300">runphp@qq.com / 123456789</code>
                        </div>
                    </div>
                    <flux:button class="mt-6 w-full !h-11 !rounded-xl !font-semibold" variant="primary" href="https://lasaas.doulingvip.com/login" target="_blank">
                        进入演示环境
                        <flux:icon.arrow-right class="size-4 ml-1" />
                    </flux:button>
                </div>
                <div class="group rounded-2xl border border-zinc-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-zinc-300 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-zinc-700 to-zinc-900 shadow-lg shadow-zinc-500/20 dark:from-zinc-600 dark:to-zinc-800">
                        <svg class="h-6 w-6 text-zinc-900 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900 dark:text-white">开源地址</h3>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">查看源码，Star 支持我们</p>
                    <div class="mt-6 space-y-3 rounded-xl bg-zinc-50/80 p-5 dark:bg-zinc-800/80">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400 dark:text-zinc-500">仓库地址</span>
                            <code class="rounded-lg bg-zinc-200 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">github.com/runphp/lasaas</code>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400 dark:text-zinc-500">许可证</span>
                            <span class="rounded-lg bg-green-50 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-300">MIT · 永久免费</span>
                        </div>
                    </div>
                    <flux:button class="mt-6 w-full !h-11 !rounded-xl" variant="outline" href="https://github.com/runphp/lasaas" target="_blank">
                        <flux:icon.code-bracket class="size-4" />
                        查看 GitHub
                    </flux:button>
                </div>
            </div>
        </div>
    </section>

    {{-- Bottom CTA --}}
    <section class="relative overflow-hidden border-t border-zinc-100 dark:border-zinc-800/50">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50/30 to-indigo-50/30 dark:from-blue-950/10 dark:to-indigo-950/10"></div>
        <div class="relative mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">准备好构建你的 SaaS 了吗？</h2>
            <p class="mt-4 text-lg text-zinc-500 dark:text-zinc-400">克隆项目，一条命令初始化，明天就能给你的客户演示。</p>
            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <flux:button variant="primary" href="{{ route('register') }}" wire:navigate class="!h-12 !text-base !rounded-xl !px-8 !font-semibold shadow-lg shadow-blue-500/25">
                    免费注册，立即开始
                    <flux:icon.arrow-right class="size-4 ml-1" />
                </flux:button>
                <flux:button variant="outline" href="{{ localizedRoute('pricing', ['locale' => app()->getLocale()]) }}" wire:navigate class="!h-12 !text-base !rounded-xl">
                    查看定价方案
                </flux:button>
            </div>
        </div>
    </section>

</flux:main>
