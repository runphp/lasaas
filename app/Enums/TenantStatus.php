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
        return __('filament-resources.tenant.statuses.'.$this->value);
    }
}
