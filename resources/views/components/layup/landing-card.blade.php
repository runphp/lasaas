@php
    $color = $data['color'] ?? 'blue';
    $iconBg = match ($color) {
        'green' => 'from-green-100 to-emerald-200 dark:from-green-900/50 dark:to-emerald-800/50',
        'purple' => 'from-purple-100 to-violet-200 dark:from-purple-900/50 dark:to-violet-800/50',
        'amber' => 'from-amber-100 to-amber-200 dark:from-amber-900/50 dark:to-amber-800/50',
        'rose' => 'from-rose-100 to-pink-200 dark:from-rose-900/50 dark:to-pink-800/50',
        'teal' => 'from-teal-100 to-cyan-200 dark:from-teal-900/50 dark:to-cyan-800/50',
        'indigo' => 'from-indigo-100 to-indigo-200 dark:from-indigo-900/50 dark:to-indigo-800/50',
        'orange' => 'from-orange-100 to-amber-200 dark:from-orange-900/50 dark:to-amber-800/50',
        'zinc' => 'from-zinc-100 to-zinc-200 dark:from-zinc-800/50 dark:to-zinc-700/50',
        default => 'from-blue-100 to-blue-200 dark:from-blue-900/50 dark:to-blue-800/50',
    };
    $iconColor = match ($color) {
        'green' => 'text-green-600 dark:text-green-400',
        'purple' => 'text-purple-600 dark:text-purple-400',
        'amber' => 'text-amber-600 dark:text-amber-400',
        'rose' => 'text-rose-600 dark:text-rose-400',
        'teal' => 'text-teal-600 dark:text-teal-400',
        'indigo' => 'text-indigo-600 dark:text-indigo-400',
        'orange' => 'text-orange-600 dark:text-orange-400',
        'zinc' => 'text-zinc-600 dark:text-zinc-400',
        default => 'text-blue-600 dark:text-blue-400',
    };
@endphp

<flux:card class="group h-full transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
    @if (filled($data['icon'] ?? null))
        <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $iconBg }}">
            <span class="text-2xl {{ $iconColor }}">{{ $data['icon'] }}</span>
        </div>
    @endif
    @if (filled($data['title'] ?? null))
        <flux:heading class="mt-5 !text-base">{{ $data['title'] }}</flux:heading>
    @endif
    @if (filled($data['description'] ?? null))
        <flux:text class="mt-2">{{ $data['description'] }}</flux:text>
    @endif
</flux:card>
