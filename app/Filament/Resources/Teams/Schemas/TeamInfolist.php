<?php

namespace App\Filament\Resources\Teams\Schemas;

use App\Models\Team;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TeamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('models.team.name')),
                TextEntry::make('slug')
                    ->label(__('models.team.slug')),
                IconEntry::make('is_personal')
                    ->label(__('models.team.is_personal'))
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label(__('validation.attributes.created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('validation.attributes.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->label(__('models.team.deleted_at'))
                    ->dateTime()
                    ->visible(fn (Team $record): bool => $record->trashed()),
            ]);
    }
}
