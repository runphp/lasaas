<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('slug')
                    ->label(__('models.page.slug')),
                TextEntry::make('title')
                    ->label(__('models.page.title')),
                TextEntry::make('layout')
                    ->label(__('models.page.layout')),
                TextEntry::make('meta')
                    ->label(__('models.page.meta'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('content')
                    ->label(__('models.page.content'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('is_published')
                    ->label(__('models.page.is_published'))
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label(__('validation.attributes.created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('validation.attributes.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
