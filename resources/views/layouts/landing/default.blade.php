<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">

{{-- Header --}}
<flux:header sticky collapsible="mobile"
             class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    {{-- 移动端：显示 toggle 按钮 --}}
    <flux:sidebar.toggle icon="bars-2" inset="left" class="lg:hidden"/>
    <div class="max-lg:flex-1 max-lg:flex max-lg:items-center max-lg:justify-center">
        <x-app-logo href="{{ localizedRoute('home', ['locale' => app()->getLocale()]) }}" wire:navigate />
    </div>
    <flux:navbar class="hidden lg:flex">
        <flux:navbar.item :href="localizedRoute('home', ['locale' => app()->getLocale()])"
                          :current="request()->routeIs('home')" wire:navigate>
            {{ __('Home') }}
        </flux:navbar.item>
        <flux:navbar.item :href="localizedRoute('features', ['locale' => app()->getLocale()])"
                          :current="request()->routeIs('features')" wire:navigate>
            {{ __('Features') }}
        </flux:navbar.item>
        <flux:navbar.item :href="localizedRoute('pricing', ['locale' => app()->getLocale()])"
                          :current="request()->routeIs('pricing')" wire:navigate>
            {{ __('Pricing') }}
        </flux:navbar.item>
        <flux:navbar.item :href="localizedRoute('about', ['locale' => app()->getLocale()])"
                          :current="request()->routeIs('about')" wire:navigate>
            {{ __('About') }}
        </flux:navbar.item>
    </flux:navbar>


    <flux:spacer/>

    <div class="hidden lg:flex items-center gap-2">
        <livewire:locale-switcher/>
        <livewire:theme-switcher/>

        <flux:navbar>
            @auth
                <flux:navbar.item href="{{ route('dashboard') }}" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
            @else
                <flux:navbar.item variant="ghost" href="{{ route('login') }}" wire:navigate>
                    {{ __('Log in') }}
                </flux:navbar.item>

                @if (Route::has('register'))
                    <flux:navbar.item href="{{ route('register') }}" wire:navigate>
                        {{ __('Register') }}
                    </flux:navbar.item>
                @endif
            @endauth
        </flux:navbar>
    </div>
</flux:header>
<!-- Mobile User Menu -->
<flux:sidebar collapsible="mobile" class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.header>
        <x-app-logo href="{{ localizedRoute('home', ['locale' => app()->getLocale()]) }}" wire:navigate />
        <flux:sidebar.collapse />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        <flux:sidebar.item icon="home" :href="localizedRoute('home', ['locale' => app()->getLocale()])"
                           :current="request()->routeIs('home')" wire:navigate>
            {{ __('Home') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="sparkles" :href="localizedRoute('features', ['locale' => app()->getLocale()])"
                           :current="request()->routeIs('features')" wire:navigate>
            {{ __('Features') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="currency-dollar" :href="localizedRoute('pricing', ['locale' => app()->getLocale()])"
                           :current="request()->routeIs('pricing')" wire:navigate>
            {{ __('Pricing') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="information-circle" :href="localizedRoute('about', ['locale' => app()->getLocale()])"
                           :current="request()->routeIs('about')" wire:navigate>
            {{ __('About') }}
        </flux:sidebar.item>
    </flux:sidebar.nav>
    <flux:spacer />
    <div class="flex items-center gap-2 px-4 py-2">
        <livewire:locale-switcher/>
        <livewire:theme-switcher/>
    </div>

    {{-- 移动端认证按钮 --}}
    <flux:sidebar.nav>
        @auth
            <flux:sidebar.item icon="layout-grid" href="{{ route('dashboard') }}" wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>
        @else
            <flux:sidebar.item icon="arrow-right-end-on-rectangle" variant="ghost" href="{{ route('login') }}" wire:navigate>
                {{ __('Log in') }}
            </flux:sidebar.item>

            @if (Route::has('register'))
                <flux:sidebar.item icon="user-plus" href="{{ route('register') }}" wire:navigate>
                    {{ __('Register') }}
                </flux:sidebar.item>
            @endif
        @endauth
    </flux:sidebar.nav>
</flux:sidebar>
{{-- Main content --}}
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
    {{ $slot }}
</div>

<livewire:landing-footer/>

@persist('toast')
<flux:toast.group>
    <flux:toast/>
</flux:toast.group>
@endpersist

@fluxScripts
</body>
</html>
