<?php

namespace App\Enums;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Yearly  = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => __('Monthly'),
            self::Yearly  => __('Yearly'),
        };
    }

    public function days(): int
    {
        return match ($this) {
            self::Monthly => 30,
            self::Yearly  => 365,
        };
    }
}
