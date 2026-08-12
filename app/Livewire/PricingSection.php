<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\BillingCycle;
use App\Models\Plan;
use Livewire\Component;

/**
 * 定价区块：运行时从数据库读取 Plan，支持月付/年付切换。
 *
 * 作为 layup 动态 widget（App\Layup\Widgets\LandingPricingWidget）的
 * 前端 Livewire 组件被渲染，widget data 通过 mount($data) 注入。
 */
class PricingSection extends Component
{
    public array $data = [];

    public BillingCycle $billingCycle = BillingCycle::Monthly;

    public function mount(array $data = []): void
    {
        $this->data = $data;
    }

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

    public function render()
    {
        return view('livewire.pricing-section');
    }
}
