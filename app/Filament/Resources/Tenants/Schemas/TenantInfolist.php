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
                    ->label('ID'),
                TextEntry::make('name')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('team.name')
                    ->label('Team')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('expired_at')
                    ->dateTime()
                    ->placeholder('-'),
                RepeatableEntry::make('domains')
                    ->schema([
                        TextEntry::make('domain'),
                    ])
                    ->columnSpanFull(),
                Section::make('数据库连接')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('tenantDatabase.database')
                            ->label('数据库名')
                            ->placeholder('-'),
                        TextEntry::make('tenantDatabase.connection')
                            ->label('数据库类型')
                            ->placeholder('-'),
                        TextEntry::make('tenantDatabase.host')
                            ->label('主机')
                            ->placeholder('-'),
                        TextEntry::make('tenantDatabase.port')
                            ->label('端口')
                            ->placeholder('-'),
                        TextEntry::make('tenantDatabase.username')
                            ->label('用户名')
                            ->placeholder('-'),
                        TextEntry::make('tenantDatabase.charset')
                            ->label('字符集')
                            ->placeholder('-'),
                    ])
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
