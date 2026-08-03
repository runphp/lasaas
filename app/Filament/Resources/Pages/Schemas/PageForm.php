<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label(__('models.page.slug'))
                    ->required(),
                TextInput::make('title')
                    ->label(__('models.page.title'))
                    ->required(),
                TextInput::make('layout')
                    ->label(__('models.page.layout'))
                    ->required()
                    ->default('landing.default'),
                Textarea::make('meta')
                    ->label(__('models.page.meta'))
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->label(__('models.page.content'))
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->label(__('models.page.is_published'))
                    ->required(),
            ]);
    }
}
