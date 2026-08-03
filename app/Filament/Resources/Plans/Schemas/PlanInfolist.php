<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament-resources.plan.sections.basic'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('models.plan.name')),
                        TextEntry::make('slug')
                            ->label(__('models.plan.slug')),
                        TextEntry::make('description')
                            ->label(__('models.plan.description'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('badge')
                            ->label(__('models.plan.badge'))
                            ->placeholder('-'),
                    ]),

                Section::make(__('filament-resources.plan.sections.pricing'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('price')
                            ->label(__('models.plan.price'))
                            ->money('CNY'),
                        TextEntry::make('original_price')
                            ->label(__('models.plan.original_price'))
                            ->money('CNY')
                            ->placeholder('-'),
                        TextEntry::make('billing_cycle')
                            ->label(__('models.plan.billing_cycle'))
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state->label()),
                    ]),

                Section::make(__('filament-resources.plan.sections.features'))
                    ->schema([
                        KeyValueEntry::make('features')
                            ->label(__('models.plan.features'))
                            ->keyLabel(__('filament-resources.plan.key_value.feature'))
                            ->valueLabel(__('filament-resources.plan.key_value.limit')),
                    ]),

                Section::make(__('filament-resources.plan.sections.display'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('sort_order')
                            ->label(__('models.plan.sort_order')),
                        IconEntry::make('is_featured')
                            ->label(__('models.plan.is_featured'))
                            ->boolean(),
                        IconEntry::make('is_active')
                            ->label(__('models.plan.is_active'))
                            ->boolean(),
                    ]),

                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('validation.attributes.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('validation.attributes.updated_at'))
                            ->dateTime(),
                    ]),
            ]);
    }
}
