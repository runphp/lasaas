@php
    $color = $data['color'] ?? 'blue';
    $iconBg = match ($color) {
        'violet' => 'from-violet-100 to-violet-200 dark:from-violet-900/50 dark:to-violet-800/50',
        'amber' => 'from-amber-100 to-amber-200 dark:from-amber-900/50 dark:to-amber-800/50',
        'emerald' => 'from-emerald-100 to-emerald-200 dark:from-emerald-900/50 dark:to-emerald-800/50',
        'rose' => 'from-rose-100 to-rose-200 dark:from-rose-900/50 dark:to-rose-800/50',
        default => 'from-blue-100 to-blue-200 dark:from-blue-900/50 dark:to-blue-800/50',
    };
@endphp

<flux:card class="group h-full text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
    @if (filled($data['emoji'] ?? null))
        <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br {{ $iconBg }} text-2xl">{{ $data['emoji'] }}</div>
    @endif
    @if (filled($data['title'] ?? null))
        <flux:heading class="mt-5 !text-base">{{ $data['title'] }}</flux:heading>
    @endif
    @if (filled($data['description'] ?? null))
        <flux:text class="mt-2 !text-sm">{{ $data['description'] }}</flux:text>
    @endif
</flux:card>
