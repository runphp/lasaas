<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<footer class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-8">

            {{-- Brand --}}
            <div class="space-y-4">
                <x-app-logo href="{{ localizedRoute('home') }}" wire:navigate />
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Building better experiences, one app at a time.') }}
                </p>
            </div>

            {{-- Products --}}
            <div>
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Products') }}</h3>
                <ul class="mt-4 space-y-3">
                    <li>
                        <a href="{{ localizedRoute('features', ['locale' => app()->getLocale()]) }}" wire:navigate class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Features') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ localizedRoute('pricing', ['locale' => app()->getLocale()]) }}" wire:navigate class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
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

            {{-- Packages --}}
            <div>
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Packages') }}</h3>
                <ul class="mt-4 space-y-3">
                    <li>
                        <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Starter') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Professional') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Enterprise') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Resources --}}
            <div>
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Resources') }}</h3>
                <ul class="mt-4 space-y-3">
                    <li>
                        <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Blog') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Documentation') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Community') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Partners --}}
            <div>
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Partners') }}</h3>
                <ul class="mt-4 space-y-3">
                    <li>
                        <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Become a Partner') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                            {{ __('Affiliates') }}
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
