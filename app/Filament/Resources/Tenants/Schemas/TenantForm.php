<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Enums\TenantStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('status')
                    ->options(TenantStatus::class)
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('expired_at'),
                Textarea::make('data')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
