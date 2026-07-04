<?php

namespace App\Filament\Resources\Modules\Schemas;

use App\Enums\ModuleStatus;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options(ModuleStatus::class)
                    ->default('inactive')
                    ->required(),
            ]);
    }
}
