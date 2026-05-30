<?php

use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::landing')] class extends Component
{
    public function mount(): void
    {
        $page = Page::findBySlug('pricing');
        view()->share('title', $page?->title ?? __('Pricing'));
    }
}; ?>

<flux:main>
    <livewire:pricing-section />
</flux:main>
