<?php

namespace App\Filament\Resources\Modules\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ModuleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('package_name'),
                TextEntry::make('name'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('version')
                    ->placeholder('-'),
                TextEntry::make('provider_class'),
                TextEntry::make('weight')
                    ->numeric(),
                TextEntry::make('dependencies')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('after')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('areas')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('path'),
                TextEntry::make('status'),
                TextEntry::make('installed_at')
                    ->dateTime()
                    ->placeholder('未安装'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
