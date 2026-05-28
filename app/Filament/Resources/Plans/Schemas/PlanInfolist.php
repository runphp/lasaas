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
                Section::make(__('Basic Info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Name')),
                        TextEntry::make('slug')
                            ->label(__('Slug')),
                        TextEntry::make('description')
                            ->label(__('Description'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('badge')
                            ->label(__('Badge'))
                            ->placeholder('-'),
                    ]),

                Section::make(__('Pricing'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('price')
                            ->label(__('Price'))
                            ->money('CNY'),
                        TextEntry::make('original_price')
                            ->label(__('Original Price'))
                            ->money('CNY')
                            ->placeholder('-'),
                        TextEntry::make('billing_cycle')
                            ->label(__('Billing Cycle'))
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state->label()),
                    ]),

                Section::make(__('Features'))
                    ->schema([
                        KeyValueEntry::make('features')
                            ->keyLabel(__('Feature'))
                            ->valueLabel(__('Limit')),
                    ]),

                Section::make(__('Display'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('sort_order')
                            ->label(__('Sort Order')),
                        IconEntry::make('is_featured')
                            ->label(__('Featured'))
                            ->boolean(),
                        IconEntry::make('is_active')
                            ->label(__('Active'))
                            ->boolean(),
                    ]),

                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('Created at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('Updated at'))
                            ->dateTime(),
                    ]),
            ]);
    }
}
