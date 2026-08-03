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
                TextEntry::make('package_name')
                    ->label(__('models.module.package_name')),
                TextEntry::make('name')
                    ->label(__('models.module.name')),
                TextEntry::make('description')
                    ->label(__('models.module.description'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('version')
                    ->label(__('models.module.version'))
                    ->placeholder('-'),
                TextEntry::make('providers')
                    ->label(__('models.module.providers')),
                TextEntry::make('weight')
                    ->label(__('models.module.weight'))
                    ->numeric(),
                TextEntry::make('dependencies')
                    ->label(__('models.module.dependencies'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('after')
                    ->label(__('models.module.after'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('areas')
                    ->label(__('models.module.areas'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('path')
                    ->label(__('models.module.path')),
                TextEntry::make('status')
                    ->label(__('models.module.status')),
                TextEntry::make('installed_at')
                    ->label(__('models.module.installed_at'))
                    ->dateTime()
                    ->placeholder(__('models.module.placeholders.installed_at')),
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
