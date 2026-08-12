<section class="relative overflow-hidden border-t border-zinc-100 dark:border-zinc-800/50">
    <div class="absolute inset-0 bg-gradient-to-b from-blue-50/30 to-indigo-50/30 dark:from-blue-950/10 dark:to-indigo-950/10"></div>
    <div class="relative mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 lg:px-8">
        @if (filled($data['heading'] ?? null))
            <flux:heading size="lg">{{ $data['heading'] }}</flux:heading>
        @endif
        @if (filled($data['description'] ?? null))
            <flux:text class="mt-4 !text-lg">
                {!! $data['description'] !!}
            </flux:text>
        @endif
        @if (filled($data['button_primary_text'] ?? null) || filled($data['button_secondary_text'] ?? null))
            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                @if (filled($data['button_primary_text'] ?? null))
                    <flux:button variant="primary" :href="$data['button_primary_url'] ?? '#'" wire:navigate class="!h-12 !text-base !rounded-xl !px-8 !font-semibold shadow-lg shadow-blue-500/25">
                        {{ $data['button_primary_text'] }}
                        <flux:icon.arrow-right class="size-4 ml-1" />
                    </flux:button>
                @endif
                @if (filled($data['button_secondary_text'] ?? null))
                    <flux:button variant="outline" :href="$data['button_secondary_url'] ?? '#'" wire:navigate class="!h-12 !text-base !rounded-xl">
                        {{ $data['button_secondary_text'] }}
                    </flux:button>
                @endif
            </div>
        @endif

        @if (filled($data['contact_value'] ?? null))
            <div class="mt-10">
                @if (filled($data['contact_label'] ?? null))
                    <flux:heading size="base" class="!font-semibold">{{ $data['contact_label'] }}</flux:heading>
                @endif
                <div class="mt-4 inline-flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-8 py-4 text-lg font-semibold text-green-700 shadow-sm dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.952-7.062-6.122zm-2.18 2.769c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982z"/></svg>
                    <span class="whitespace-nowrap">{{ $data['contact_value'] }}</span>
                </div>
            </div>
        @endif
    </div>
</section>
