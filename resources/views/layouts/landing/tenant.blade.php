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
