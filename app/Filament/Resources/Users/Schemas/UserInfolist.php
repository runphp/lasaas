<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('models.user.name')),
                TextEntry::make('email')
                    ->label(__('models.user.email')),
                TextEntry::make('email_verified_at')
                    ->label(__('models.user.email_verified_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('roles.name')
                    ->label(__('models.user.roles.name'))
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->placeholder('-'),
                TextEntry::make('currentTeam.name')
                    ->label(__('models.user.current_team.name'))
                    ->placeholder('-'),
                TextEntry::make('two_factor_secret')
                    ->label(__('models.user.two_factor_secret'))
                    ->formatStateUsing(function () {
                        return '•••••• '.__('models.user.text.has_two_factor');
                    })
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('two_factor_recovery_codes')
                    ->label(__('models.user.two_factor_recovery_codes'))
                    ->formatStateUsing(function () {
                        return '•••••• '.__('models.user.text.has_recovery_codes');
                    })
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('two_factor_confirmed_at')
                    ->label(__('models.user.two_factor_confirmed_at'))
                    ->dateTime()
                    ->placeholder('-')
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
