<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label(__('models.tenant.id')),
                TextEntry::make('name')
                    ->label(__('models.tenant.name'))
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label(__('models.tenant.email'))
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->label(__('models.tenant.phone'))
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label(__('models.tenant.user')),
                TextEntry::make('team.name')
                    ->label(__('models.tenant.team'))
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->label(__('models.tenant.status'))
                    ->badge(),
                TextEntry::make('expired_at')
                    ->label(__('models.tenant.expired_at'))
                    ->dateTime()
                    ->placeholder('-'),
                RepeatableEntry::make('domains')
                    ->label(__('models.tenant.domains'))
                    ->schema([
                        TextEntry::make('domain'),
                    ])
                    ->columnSpanFull(),
                Section::make(__('filament-resources.tenant.database.section'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('tenantDatabase.database')
                            ->label(__('models.tenant.database.database'))
                            ->placeholder('-'),
                        TextEntry::make('tenantDatabase.connection')
                            ->label(__('models.tenant.database.connection'))
                            ->placeholder('-'),
                        TextEntry::make('tenantDatabase.host')
                            ->label(__('models.tenant.database.host'))
                            ->placeholder('-'),
                        TextEntry::make('tenantDatabase.port')
                            ->label(__('models.tenant.database.port'))
                            ->placeholder('-'),
                        TextEntry::make('tenantDatabase.username')
                            ->label(__('models.tenant.database.username'))
                            ->placeholder('-'),
                        TextEntry::make('tenantDatabase.charset')
                            ->label(__('models.tenant.database.charset'))
                            ->placeholder('-'),
                    ])
                    ->columnSpanFull(),
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
