<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">

{{-- Header --}}
<flux:header sticky collapsible="mobile"
             class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle icon="bars-2" inset="left" class="lg:hidden"/>
    <div class="max-lg:flex-1 max-lg:flex max-lg:items-center max-lg:justify-center">
        <flux:heading size="base" class="truncate">
            {{ tenant()?->name ?? __('Tenant') }}
        </flux:heading>
    </div>
    <flux:spacer/>
    <div class="hidden lg:flex items-center gap-2">
        <livewire:locale-switcher/>
        <livewire:theme-switcher/>

        <flux:navbar>
            @auth
                <flux:navbar.item href="/admin" wire:navigate>
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

{{-- Mobile Sidebar --}}
<flux:sidebar collapsible="mobile" class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.header>
        <flux:heading size="base" class="truncate">
            {{ tenant()?->name ?? __('Tenant') }}
        </flux:heading>
        <flux:sidebar.collapse />
    </flux:sidebar.header>
    <flux:spacer />
    <div class="flex items-center gap-2 px-4 py-2">
        <livewire:locale-switcher/>
        <livewire:theme-switcher/>
    </div>

    {{-- Mobile auth buttons --}}
    <flux:sidebar.nav>
        @auth
            <flux:sidebar.item icon="layout-grid" href="/admin" wire:navigate>
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

@persist('toast')
<flux:toast.group>
    <flux:toast/>
</flux:toast.group>
@endpersist

@fluxScripts
</body>
</html>
