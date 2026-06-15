<?php

use Flux\Flux;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use LaravelLang\Locales\Facades\Locales;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Locale settings')]
class extends Component {
    public string $locale;

    public function mount(): void
    {
        $this->locale = App::currentLocale();
    }

    public function updatedLocale(string $value): void
    {
        if ($user = Auth::user()) {
            $user->forceFill(['locale' => $value])->save();
        }

        App::setLocale($value);

        Flux::toast(variant: 'success', text: __('Locale updated.'));

        $this->redirectRoute('locale.edit',navigate: true);
    }

    #[Computed]
    public function locales()
    {
        return Locales::installed();
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Locale settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Locale')" :subheading="__('Choose your preferred language')">
        <flux:radio.group wire:model.live="locale" variant="segmented">
            @foreach ($this->locales as $item)
                <flux:radio value="{{ $item->code }}">
                    {{ $item->native }}
                </flux:radio>
            @endforeach
        </flux:radio.group>
    </x-pages::settings.layout>
</section>
