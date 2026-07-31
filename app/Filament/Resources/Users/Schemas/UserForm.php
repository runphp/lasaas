<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Select::make('roles')
                    ->options(fn () => Role::pluck('name', 'name'))
                    ->multiple()
                    ->preload(),
                DateTimePicker::make('email_verified_at')
                    ->hiddenOn('edit'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state)),
                Select::make('current_team_id')
                    ->relationship('currentTeam', 'name')
                    ->options(function ($record) {
                        return $record?->teams()->pluck('teams.name', 'teams.id') ?? [];
                    })
                    ->default(null),
                Textarea::make('two_factor_secret')
                    ->default(null)
                    ->columnSpanFull()
                    ->hiddenOn('edit'),
                Textarea::make('two_factor_recovery_codes')
                    ->default(null)
                    ->columnSpanFull()
                    ->hiddenOn('edit'),
                DateTimePicker::make('two_factor_confirmed_at')
                    ->hiddenOn('edit'),
            ]);
    }
}
