<?php

namespace App\Filament\Resources\Plans\Schemas;

use App\Enums\BillingCycle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament-resources.plan.sections.basic'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('models.plan.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label(__('models.plan.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label(__('models.plan.description'))
                            ->columnSpanFull()
                            ->maxLength(500),
                        TextInput::make('badge')
                            ->label(__('models.plan.badge'))
                            ->maxLength(50)
                            ->hint(__('filament-resources.plan.hints.badge')),
                    ]),

                Section::make(__('filament-resources.plan.sections.pricing'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')
                            ->label(__('models.plan.price'))
                            ->numeric()
                            ->prefix('¥')
                            ->default(0)
                            ->required(),
                        TextInput::make('original_price')
                            ->label(__('models.plan.original_price'))
                            ->numeric()
                            ->prefix('¥')
                            ->nullable()
                            ->hint(__('filament-resources.plan.hints.original_price')),
                        Select::make('billing_cycle')
                            ->label(__('models.plan.billing_cycle'))
                            ->options(BillingCycle::class)
                            ->default(BillingCycle::Monthly)
                            ->required(),
                    ]),

                Section::make(__('filament-resources.plan.sections.features'))
                    ->schema([
                        KeyValue::make('features')
                            ->label(__('models.plan.features'))
                            ->keyLabel(__('filament-resources.plan.key_value.feature'))
                            ->valueLabel(__('filament-resources.plan.key_value.limit'))
                            ->addActionLabel(__('filament-resources.plan.key_value.add_feature'))
                            ->default([
                                'max_users' => '10',
                                'max_teams' => '5',
                            ]),
                    ]),

                Section::make(__('filament-resources.plan.sections.display'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('sort_order')
                            ->label(__('models.plan.sort_order'))
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_featured')
                            ->label(__('models.plan.is_featured')),
                        Toggle::make('is_active')
                            ->label(__('models.plan.is_active'))
                            ->default(true),
                    ]),
            ]);
    }
}
