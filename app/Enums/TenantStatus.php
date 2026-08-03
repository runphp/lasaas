<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TenantStatus: string implements HasLabel
{
    case PENDING = 'pending';

    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case EXPIRED = 'expired';
    case DISABLED = 'disabled';

    public function getLabel(): ?string
    {
        return __('models.tenant.statuses.'.$this->value);
    }

    public function getColorClass(): string
    {
        return match ($this) {
            self::PENDING => 'text-amber-500',
            self::ACTIVE => 'text-green-500',
            self::SUSPENDED => 'text-orange-500',
            self::EXPIRED => 'text-red-500',
            self::DISABLED => 'text-gray-500',
        };
    }
}
