<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<div x-data>
    <flux:dropdown position="bottom" align="end">
        <flux:button variant="ghost" size="sm" icon:trailing="chevron-down">
            <div x-cloak x-show="$flux.appearance === 'light'" class="flex items-center gap-2">
                <flux:icon.sun class="size-4" />
                {{ __('Light') }}
            </div>
            <div x-cloak x-show="$flux.appearance === 'dark'" class="flex items-center gap-2">
                <flux:icon.moon class="size-4" />
                {{ __('Dark') }}
            </div>
            <div x-cloak x-show="$flux.appearance === 'system'" class="flex items-center gap-2">
                <flux:icon.computer-desktop class="size-4" />
                {{ __('System') }}
            </div>
        </flux:button>
        <flux:menu>
            <flux:menu.item x-on:click="$flux.appearance = 'light'">
                <div class="flex items-center gap-2">
                    <flux:icon.sun class="size-4" />
                    {{ __('Light') }}
                </div>
            </flux:menu.item>
            <flux:menu.item x-on:click="$flux.appearance = 'dark'">
                <div class="flex items-center gap-2">
                    <flux:icon.moon class="size-4" />
                    {{ __('Dark') }}
                </div>
            </flux:menu.item>
            <flux:menu.item x-on:click="$flux.appearance = 'system'">
                <div class="flex items-center gap-2">
                    <flux:icon.computer-desktop class="size-4" />
                    {{ __('System') }}
                </div>
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</div>
