@props([
    'size' => null,
])

@php
$classes = Flux::classes()
    ->add('bg-white dark:bg-zinc-900')
    ->add('border border-zinc-200 dark:border-zinc-800')
    ->add(match ($size) {
        default => 'p-6 rounded-2xl',
        'sm' => 'p-4 rounded-lg',
    })
    ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-card>
    {{ $slot }}
</div>
