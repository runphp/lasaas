<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ModuleStatus: string implements HasLabel
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function getLabel(): ?string
    {
        return __('filament-resources.module.statuses.'.$this->value);
    }
}
