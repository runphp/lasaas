<?php

namespace App\Filament\Resources\Plans\Schemas;

use App\Enums\BillingCycle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Basic Info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->columnSpanFull()
                            ->maxLength(500),
                        TextInput::make('badge')
                            ->label(__('Badge'))
                            ->maxLength(50)
                            ->hint(__('e.g. Recommended, Hot')),
                    ]),

                Section::make(__('Pricing'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')
                            ->label(__('Price'))
                            ->numeric()
                            ->prefix('¥')
                            ->default(0)
                            ->required(),
                        TextInput::make('original_price')
                            ->label(__('Original Price'))
                            ->numeric()
                            ->prefix('¥')
                            ->nullable()
                            ->hint(__('Strikethrough original price, leave empty if no discount')),
                        Select::make('billing_cycle')
                            ->label(__('Billing Cycle'))
                            ->options(BillingCycle::class)
                            ->default(BillingCycle::Monthly)
                            ->required(),
                    ]),

                Section::make(__('Features'))
                    ->schema([
                        KeyValue::make('features')
                            ->keyLabel(__('Feature'))
                            ->valueLabel(__('Limit'))
                            ->addActionLabel(__('Add feature'))
                            ->default([
                                'max_users' => '10',
                                'max_teams' => '5',
                            ]),
                    ]),

                Section::make(__('Display'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('sort_order')
                            ->label(__('Sort Order'))
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_featured')
                            ->label(__('Featured')),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                    ]),
            ]);
    }
}
