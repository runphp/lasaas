<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if(auth()->user()->canAccessPanel(filament()->getDefaultPanel()))
            <div class="flex items-center justify-between rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div>
                    <h2 class="text-lg font-semibold">{{ __('Central Admin Panel') }}</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Manage tenants, teams, users, roles and permissions.') }}</p>
                </div>
                <flux:button icon="lock-closed" href="{{ route('filament.admin.pages.dashboard') }}" target="_blank">
                    {{ __('Open Admin') }}
                </flux:button>
            </div>
        @endif
        <livewire:tenant-list />
        @php
            $moduleCards = new \Illuminate\Support\Collection;
            event(new \App\Events\FrontendDashboardCardsCollecting($moduleCards, 'central'));
        @endphp

        @if ($moduleCards->isNotEmpty())
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                @foreach ($moduleCards as $card)
                    <a href="{{ $card['url'] }}" class="group flex flex-col gap-2 rounded-xl border border-neutral-200 p-4 transition hover:border-neutral-300 dark:border-neutral-700 dark:hover:border-neutral-600">
                        <div class="flex items-center gap-2">
                            @if (($card['icon'] ?? null) !== null)
                                <flux:icon :name="$card['icon']" variant="micro" />
                            @endif
                            <h3 class="font-semibold">{{ $card['title'] }}</h3>
                        </div>
                        @if (($card['description'] ?? null) !== null)
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $card['description'] }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app>
