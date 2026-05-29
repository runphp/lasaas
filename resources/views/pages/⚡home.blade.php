<?php

use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::landing')] class extends Component
{
    public function mount(): void
    {
        $page = Page::findBySlug('home');
        view()->share('title', $page?->title ?? __('Welcome'));
    }
}; ?>

<flux:main>

    {{-- Hero Section --}}
    <section class="py-32 mb-16">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-5xl lg:text-6xl">
                {{ __('SaaS Starter Kit') }}
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-500 dark:text-zinc-400">
                {{ __('A modern, production-ready SaaS boilerplate built with Laravel, Livewire & Flux. Multi-tenant, team collaboration, two-factor auth, and more — everything you need to launch your SaaS product.') }}
            </p>
        </div>
    </section>

    {{-- Features Section --}}
    <section class="border-t border-zinc-200 bg-zinc-50 py-28 mb-16 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-3xl">
                    {{ __('Why Lasaas') }}
                </h2>
                <p class="mt-4 text-base text-zinc-500 dark:text-zinc-400">
                    {{ __('Everything you need to build a modern multi-tenant SaaS platform, out of the box.') }}
                </p>
            </div>
            <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex size-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                        <flux:icon.server-stack class="size-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Multi-Tenant Architecture') }}</h3>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Isolated databases for each tenant with custom domain support. Data security and privacy by design.') }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex size-12 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900">
                        <flux:icon.squares-plus class="size-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Filament Admin Panel') }}</h3>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Powerful admin panel for managing users, tenants, teams, roles and permissions — all in one place.') }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex size-12 items-center justify-center rounded-lg bg-pink-100 dark:bg-pink-900">
                        <flux:icon.bolt class="size-6 text-pink-600 dark:text-pink-400" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Livewire & Flux UI') }}</h3>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Full-stack reactivity without writing JavaScript. Beautiful UI components powered by Flux and Tailwind CSS.') }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex size-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                        <flux:icon.user-group class="size-6 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Team Collaboration') }}</h3>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Create teams, invite members, assign roles. Full team management with permissions built-in.') }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex size-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                        <flux:icon.shield-check class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Security & Auth') }}</h3>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Two-factor authentication, Passkeys passwordless login, RBAC permissions, and email verification.') }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex size-12 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900">
                        <flux:icon.language class="size-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Internationalization') }}</h3>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Built-in bilingual support (Chinese & English) with easy extensibility for additional languages.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Demo Section --}}
    <section class="border-t border-zinc-200 py-28 dark:border-zinc-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-3xl">
                    {{ __('Try It Yourself') }}
                </h2>
                <p class="mt-4 text-base text-zinc-500 dark:text-zinc-400">
                    {{ __('Experience the full power of our platform with the demo account below.') }}
                </p>
            </div>
            <div class="mt-10 grid gap-6 sm:grid-cols-2">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                            <flux:icon.user class="size-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Demo Account') }}</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Log in and explore the dashboard') }}</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2 rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</span>
                            <code class="rounded bg-zinc-200 px-2 py-0.5 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">runphp@qq.com</code>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Password') }}</span>
                            <code class="rounded bg-zinc-200 px-2 py-0.5 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">123456789</code>
                        </div>
                    </div>
                    <flux:button class="mt-4 w-full" href="{{ route('login') }}" wire:navigate>
                        {{ __('Sign In to Demo') }}
                    </flux:button>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                            <flux:icon.code-bracket class="size-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Open Source') }}</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Check out the source code on GitHub') }}</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2 rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Repository') }}</span>
                            <code class="rounded bg-zinc-200 px-2 py-0.5 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">github.com/runphp/lasaas</code>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('License') }}</span>
                            <span class="text-zinc-700 dark:text-zinc-300">{{ __('MIT') }}</span>
                        </div>
                    </div>
                    <flux:button class="mt-4 w-full" variant="outline" href="https://github.com/runphp/lasaas" target="_blank">
                        {{ __('View on GitHub') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </section>

    <livewire:pricing-section />

</flux:main>
