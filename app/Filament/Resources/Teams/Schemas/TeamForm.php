<?php

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('models.team.name'))
                    ->required(),
                TextInput::make('slug')
                    ->label(__('models.team.slug'))
                    ->required(),
                Toggle::make('is_personal')
                    ->label(__('models.team.is_personal'))
                    ->required(),
            ]);
    }
}
