<?php

use Illuminate\Support\Collection;
use LaravelLang\Locales\Facades\Locales;
use Livewire\Component;

new class extends Component {
    public function locales(): Collection
    {
        return Locales::installed();
    }

    public function currentCode(): string
    {
        return app()->currentLocale();
    }

    public function currentNative(): string
    {
        $current = $this->currentCode();
        return $this->locales()->firstWhere('code', $current)?->native ?? strtoupper($current);
    }
}; ?>

<div>
    <flux:dropdown position="bottom" align="end">
        <flux:button variant="ghost" size="sm" icon:trailing="chevron-down">
            <flux:icon.language class="size-4 lg:hidden" />
            <span class="max-lg:hidden">{{ $this->currentNative() }}</span>
        </flux:button>
        <flux:menu>
            @foreach ($this->locales() as $locale)
                <flux:menu.item
                    href="{{ localizedRoute('home', ['locale' => $locale->code]) }}"
                    :active="$locale->code === $this->currentCode()"
                >
                    {{ $locale->native }}
                </flux:menu.item>
            @endforeach
        </flux:menu>
    </flux:dropdown>
</div>
