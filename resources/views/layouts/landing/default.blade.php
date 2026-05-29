<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-900 antialiased" x-data="{ mobileNavOpen: false }">

{{-- Header --}}
<flux:header container class="max-lg:hidden sticky top-0 z-50 border-b border-zinc-200 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/80">
    <x-app-logo href="{{ localizedRoute('home', ['locale' => app()->getLocale()]) }}" wire:navigate />

    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item :href="localizedRoute('home', ['locale' => app()->getLocale()])" :current="request()->routeIs('home')" wire:navigate>
            {{ __('Home') }}
        </flux:navbar.item>
        <flux:navbar.item href="#">
            {{ __('Features') }}
        </flux:navbar.item>
        <flux:navbar.item href="#">
            {{ __('Pricing') }}
        </flux:navbar.item>
        <flux:navbar.item href="#">
            {{ __('About') }}
        </flux:navbar.item>
    </flux:navbar>

    <flux:spacer />

    <div class="flex items-center gap-2">
        <livewire:locale-switcher />
        <livewire:theme-switcher />

        @auth
            <flux:button href="{{ route('dashboard') }}" wire:navigate>
                {{ __('Dashboard') }}
            </flux:button>
        @else
            <flux:button variant="ghost" href="{{ route('login') }}" wire:navigate>
                {{ __('Log in') }}
            </flux:button>

            @if (Route::has('register'))
                <flux:button href="{{ route('register') }}" wire:navigate>
                    {{ __('Register') }}
                </flux:button>
            @endif
        @endauth
    </div>
</flux:header>

<!-- Mobile Header -->
<flux:header class="lg:hidden sticky top-0 z-40 border-b border-zinc-200 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/80">
    <flux:button variant="ghost" icon="bars-2" @click="mobileNavOpen = true" />

    <x-app-logo href="{{ localizedRoute('home') }}" wire:navigate />

    <flux:spacer />

    <livewire:locale-switcher />
    <livewire:theme-switcher />

    @auth
        <flux:dropdown position="top" align="end">
            <flux:profile
                :initials="auth()->user()->initials()"
                icon-trailing="chevron-down"
            />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar
                                :name="auth()->user()->name"
                                :initials="auth()->user()->initials()"
                            />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('dashboard')" icon="home" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:menu.item>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item
                        as="button"
                        type="submit"
                        icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer"
                    >
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    @else
        <flux:button variant="ghost" href="{{ route('login') }}" wire:navigate>
            {{ __('Log in') }}
        </flux:button>

        @if (Route::has('register'))
            <flux:button href="{{ route('register') }}" wire:navigate>
                {{ __('Register') }}
            </flux:button>
        @endif
    @endauth
</flux:header>

<!-- Mobile Nav Drawer -->
<div x-show="mobileNavOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" x-cloak>
    <div
        x-show="mobileNavOpen"
        x-transition.opacity
        class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm"
        @click="mobileNavOpen = false"
    ></div>

    <div
        x-show="mobileNavOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 w-72 bg-white shadow-xl dark:bg-zinc-900 dark:shadow-zinc-950"
    >
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
            <x-app-logo href="{{ localizedRoute('home') }}" wire:navigate />
            <flux:button variant="ghost" icon="x-mark" @click="mobileNavOpen = false" />
        </div>

        <nav class="mt-2 flex flex-col gap-1 px-2">
            <a
                href="{{ localizedRoute('home') }}"
                wire:navigate
                @click="mobileNavOpen = false"
                class="rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800 {{ request()->routeIs('home') ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white' : '' }}"
            >
                {{ __('Home') }}
            </a>
            <a
                href="#"
                @click="mobileNavOpen = false"
                class="rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
            >
                {{ __('Features') }}
            </a>
            <a
                href="#"
                @click="mobileNavOpen = false"
                class="rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
            >
                {{ __('Pricing') }}
            </a>
            <a
                href="#"
                @click="mobileNavOpen = false"
                class="rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
            >
                {{ __('About') }}
            </a>
        </nav>
    </div>
</div>

{{-- Main content --}}
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
    {{ $slot }}
</div>

<livewire:landing-footer />

@persist('toast')
<flux:toast.group>
    <flux:toast />
</flux:toast.group>
@endpersist

@fluxScripts
</body>
</html>
