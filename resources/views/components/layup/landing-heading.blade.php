@php
    $alignment = $data['alignment'] ?? 'center';
    $wrapperClass = $alignment === 'left' ? 'mx-auto max-w-3xl text-left' : 'mx-auto max-w-3xl text-center';
@endphp

<div class="{{ $wrapperClass }}">
    @if (filled($data['badge'] ?? null))
        <flux:badge color="blue" class="!rounded-full !px-3 !py-1 !text-xs">{{ $data['badge'] }}</flux:badge>
    @endif
    @if (filled($data['heading'] ?? null))
        <flux:heading size="lg" class="mt-6">{{ $data['heading'] }}</flux:heading>
    @endif
    @if (filled($data['description'] ?? null))
        <flux:text class="mt-4">{{ $data['description'] }}</flux:text>
    @endif
</div>
