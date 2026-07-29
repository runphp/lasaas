<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('tenant.layouts.landing')] class extends Component
{
    public string $tenantName;

    public function mount(): void
    {
        $tenant = tenant();
        $this->tenantName = $tenant->name ?? 'Tenant';

        view()->share('title', $this->tenantName);
    }
}; ?>

<div class="flex min-h-[70vh] flex-col items-center justify-center text-center">
    <flux:heading size="xl" level="1" class="!text-center">
        {{ $tenantName }}
    </flux:heading>

    <flux:text class="mt-6 max-w-lg !text-lg !leading-relaxed">
        {{ __('Welcome to :name. This site is under construction.', ['name' => $tenantName]) }}
    </flux:text>

    <div class="mt-10">
        <flux:button variant="primary" size="base" href="#" wire:navigate>
            {{ __('Learn More') }}
            <flux:icon.arrow-right class="size-4 ml-1" />
        </flux:button>
    </div>
</div>
