<?php

use App\Enums\BillingCycle;
use App\Models\Plan;
use Livewire\Component;

new class extends Component
{
    public BillingCycle $billingCycle = BillingCycle::Monthly;

    public function switchBillingCycle(string $cycle): void
    {
        $this->billingCycle = BillingCycle::from($cycle);
    }

    public function plans()
    {
        return Plan::query()
            ->where('is_active', true)
            ->where('billing_cycle', $this->billingCycle)
            ->orderBy('sort_order')
            ->get();
    }
}; ?>

<div>
    <section class="py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
                    {{ __('Pricing Plans') }}
                </h2>
                <p class="mt-4 text-lg text-zinc-500 dark:text-zinc-400">
                    {{ __('Choose the perfect plan for your needs') }}
                </p>
            </div>

            {{-- Billing Cycle Toggle --}}
            <div class="mt-8 flex justify-center">
                <div class="inline-flex rounded-lg border border-zinc-200 bg-zinc-100 p-1 dark:border-zinc-700 dark:bg-zinc-800">
                    <button
                        wire:click="switchBillingCycle('monthly')"
                        class="rounded-md px-4 py-2 text-sm font-medium transition-colors {{ $billingCycle === BillingCycle::Monthly ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    >
                        {{ __('Monthly') }}
                    </button>
                    <button
                        wire:click="switchBillingCycle('yearly')"
                        class="rounded-md px-4 py-2 text-sm font-medium transition-colors {{ $billingCycle === BillingCycle::Yearly ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    >
                        {{ __('Yearly') }}
                        @if ($billingCycle === BillingCycle::Yearly)
                            <span class="ml-1.5 rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900 dark:text-green-300">
                                {{ __('Save 20%') }}
                            </span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Plan Cards --}}
            <div class="mt-12 grid gap-8 sm:grid-cols-2">
                @foreach ($this->plans() as $plan)
                    <div class="flex flex-col rounded-2xl border p-8 transition-shadow hover:shadow-lg {{ $plan->is_featured ? 'border-blue-500 bg-blue-50/50 ring-1 ring-blue-500 dark:border-blue-400 dark:bg-blue-950/30 dark:ring-blue-400' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $plan->name }}</h3>
                            @if ($plan->badge)
                                <span class="mt-1.5 inline-block rounded-full bg-blue-100 px-3 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                    {{ $plan->badge }}
                                </span>
                            @endif
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $plan->description }}</p>
                        </div>

                        <div class="mb-6">
                            <div class="flex items-baseline gap-1">
                                @if ($plan->isFree())
                                    <span class="text-4xl font-bold text-zinc-900 dark:text-white">{{ __('Free') }}</span>
                                @else
                                    <span class="text-sm text-zinc-400 dark:text-zinc-500">{{ __('¥') }}</span>
                                    <span class="text-4xl font-bold text-zinc-900 dark:text-white">{{ number_format($plan->price, 0) }}</span>
                                    @if ($plan->hasDiscount())
                                        <span class="ml-2 text-lg text-zinc-400 line-through dark:text-zinc-500">¥{{ number_format($plan->original_price, 0) }}</span>
                                    @endif
                                    <span class="text-sm text-zinc-400 dark:text-zinc-500">/{{ $plan->billing_cycle->label() }}</span>
                                @endif
                            </div>
                        </div>

                        <ul class="mb-8 flex-1 space-y-3">
                            @foreach ($plan->features as $key => $value)
                                <li class="flex items-start gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    @if (is_bool($value))
                                        @if ($value)
                                            <flux:icon.check class="mt-0.5 size-4 shrink-0 text-green-500" />
                                        @else
                                            <flux:icon.x-mark class="mt-0.5 size-4 shrink-0 text-zinc-300 dark:text-zinc-600" />
                                        @endif
                                    @else
                                        <flux:icon.check class="mt-0.5 size-4 shrink-0 text-green-500" />
                                    @endif
                                    <span>
                                        @if (is_bool($value))
                                            {{ __("feature.{$key}") }}
                                        @elseif ($value === -1)
                                            <strong>{{ __('Unlimited') }}</strong> {{ __("feature.{$key}") }}
                                        @else
                                            <strong>{{ $value }}</strong> {{ __("feature.{$key}") }}
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>

                        <flux:button
                            :variant="$plan->is_featured ? 'primary' : 'outline'"
                            class="w-full"
                        >
                            {{ $plan->isFree() ? __('Get Started') : __('Subscribe') }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
