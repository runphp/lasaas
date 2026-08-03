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
                    ->label(__('models.user.name'))
                    ->required(),

                TextInput::make('email')
                    ->label(__('models.user.email'))
                    ->email()
                    ->required(),

                Select::make('roles')
                    ->label(__('models.user.roles.name'))
                    ->options(fn () => Role::pluck('name', 'name'))
                    ->multiple()
                    ->preload(),

                DateTimePicker::make('email_verified_at')
                    ->label(__('models.user.email_verified_at'))
                    ->hiddenOn('edit'),

                TextInput::make('password')
                    ->label(__('models.user.password'))
                    ->password()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state)),

                Select::make('current_team_id')
                    ->label(__('models.user.current_team.name'))
                    ->relationship('currentTeam', 'name')
                    ->options(function ($record) {
                        return $record?->teams()->pluck('teams.name', 'teams.id') ?? [];
                    })
                    ->default(null),

                Textarea::make('two_factor_secret')
                    ->label(__('models.user.two_factor_secret'))
                    ->default(null)
                    ->columnSpanFull()
                    ->hiddenOn('edit'),

                Textarea::make('two_factor_recovery_codes')
                    ->label(__('models.user.two_factor_recovery_codes'))
                    ->default(null)
                    ->columnSpanFull()
                    ->hiddenOn('edit'),

                DateTimePicker::make('two_factor_confirmed_at')
                    ->label(__('models.user.two_factor_confirmed_at'))
                    ->hiddenOn('edit'),
            ]);
    }
}
