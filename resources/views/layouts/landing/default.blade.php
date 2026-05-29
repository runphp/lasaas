<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900 antialiased">
        {{-- Header --}}
        <header class="sticky top-0 z-50 border-b border-zinc-200 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/80">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                {{-- Logo --}}
                <div class="flex items-center gap-8">
                    <x-app-logo href="{{ localizedRoute('home', ['locale' => App::currentLocale()]) }}" wire:navigate />

                    {{-- Navigation --}}
                    <nav class="hidden items-center gap-6 md:flex">
                        <a href="{{ localizedRoute('home') }}" @class([
                            'text-sm font-medium transition-colors hover:text-zinc-900 dark:hover:text-white',
                            'text-zinc-900 dark:text-white' => request()->routeIs('home'),
                            'text-zinc-500 dark:text-zinc-400' => ! request()->routeIs('home'),
                        ]) wire:navigate>
                            {{ __('Home') }}
                        </a>
                        <a href="#" class="text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Features') }}
                        </a>
                        <a href="#" class="text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Pricing') }}
                        </a>
                        <a href="#" class="text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('About') }}
                        </a>
                    </nav>
                </div>

                {{-- Right side: Language switcher + Auth buttons --}}
                <div class="flex items-center gap-3">
                    @php
                        $currentLocale = App::currentLocale();
                        $locales = \LaravelLang\Locales\Facades\Locales::installed();
                        $currentLocaleData = $locales->firstWhere('code', $currentLocale);
                    @endphp

                    <flux:dropdown position="bottom" align="end">
                        <flux:button variant="ghost" size="sm" icon:trailing="chevron-down">
                            {{ $currentLocaleData?->native ?? strtoupper($currentLocale) }}
                        </flux:button>
                        <flux:menu>
                            @foreach($locales as $locale)
                                <flux:menu.item
                                    href="{{ localizedRoute('home', ['locale' => $locale->code]) }}"
                                    :active="$locale->code === $currentLocale"
                                >
                                    {{ $locale->native }}
                                </flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>

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
            </div>
        </header>

        {{-- Main Content --}}
        <main>
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    {{-- Brand Column --}}
                    <div class="space-y-4">
                        <x-app-logo href="{{ localizedRoute('home') }}" wire:navigate />
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Building better experiences, one app at a time.') }}
                        </p>
                    </div>

                    {{-- Product --}}
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Product') }}</h3>
                        <ul class="mt-4 space-y-3">
                            <li>
                                <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                    {{ __('Features') }}
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                    {{ __('Pricing') }}
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                    {{ __('Changelog') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- Company --}}
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Company') }}</h3>
                        <ul class="mt-4 space-y-3">
                            <li>
                                <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                    {{ __('About') }}
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                    {{ __('Blog') }}
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                    {{ __('Careers') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- Legal --}}
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Legal') }}</h3>
                        <ul class="mt-4 space-y-3">
                            <li>
                                <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                    {{ __('Privacy Policy') }}
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                    {{ __('Terms of Service') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Bottom bar --}}
                <div class="mt-12 border-t border-zinc-200 pt-8 dark:border-zinc-800">
                    <p class="text-center text-sm text-zinc-400 dark:text-zinc-500">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. {{ __('All rights reserved.') }}
                    </p>
                </div>
            </div>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
