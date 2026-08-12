<section class="relative overflow-hidden pt-28 pb-20">
    {{-- Decorative background --}}
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[600px] rounded-full bg-gradient-to-b from-blue-100/60 via-indigo-50/30 to-transparent blur-3xl dark:from-blue-900/20 dark:via-indigo-900/10"></div>
        <div class="absolute top-20 right-0 w-[400px] h-[400px] rounded-full bg-gradient-to-l from-purple-100/40 to-transparent blur-3xl dark:from-purple-900/10"></div>
    </div>

    <div class="relative mx-auto max-w-6xl px-4 text-center sm:px-6 lg:px-8">
        @if (filled($data['badge'] ?? null))
            <flux:badge color="blue" class="mb-8 !rounded-full !px-4 !py-1.5 !text-sm">
                <span class="relative flex size-2 mr-1">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex size-2 rounded-full bg-green-500"></span>
                </span>
                {{ $data['badge'] }}
            </flux:badge>
        @endif

        <flux:heading size="xl" level="1" class="!text-center">
            {{ $data['heading_line1'] ?? '' }}
            @if (filled($data['heading_line2'] ?? null))
                <br class="sm:hidden" />
                <span class="relative inline-block">
                    <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">{{ $data['heading_line2'] }}</span>
                    <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 200 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 4 Q50 0 100 4 Q150 8 200 4" stroke="currentColor" stroke-width="2" class="text-indigo-300/60 dark:text-indigo-600/40" stroke-linecap="round"/>
                    </svg>
                </span>
            @endif
        </flux:heading>

        @if (filled($data['description'] ?? null))
            <flux:text class="mx-auto mt-8 max-w-3xl !text-lg !leading-relaxed">
                {{ $data['description'] }}
            </flux:text>
        @endif

        @if (filled($data['button_primary_text'] ?? null) || filled($data['button_secondary_text'] ?? null) || filled($data['button_ghost_text'] ?? null))
            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                @if (filled($data['button_primary_text'] ?? null))
                    <flux:button variant="primary" :href="$data['button_primary_url'] ?? '#'" wire:navigate class="!h-12 !text-base !rounded-xl !px-8 !font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-shadow">
                        {{ $data['button_primary_text'] }}
                        <flux:icon.arrow-right class="size-4 ml-1" />
                    </flux:button>
                @endif

                @if (filled($data['button_secondary_text'] ?? null))
                    <flux:button variant="outline" :href="$data['button_secondary_url'] ?? '#'" wire:navigate class="!h-12 !text-base !rounded-xl">
                        {{ $data['button_secondary_text'] }}
                    </flux:button>
                @endif

                @if (filled($data['button_ghost_text'] ?? null))
                    <flux:button variant="ghost" :href="$data['button_ghost_url'] ?? '#'" target="_blank" class="!h-12 !text-base !rounded-xl">
                        <flux:icon.code-bracket class="size-4" />
                        {{ $data['button_ghost_text'] }}
                    </flux:button>
                @endif
            </div>
        @endif
    </div>
</section>
