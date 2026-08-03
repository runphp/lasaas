<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('models.user.name'))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(__('models.user.roles.name'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('models.user.email'))
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label(__('models.user.email_verified_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('currentTeam.name')
                    ->label(__('models.user.current_team.name'))
                    ->searchable(),
                TextColumn::make('two_factor_confirmed_at')
                    ->label(__('models.user.two_factor_confirmed_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('validation.attributes.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('validation.attributes.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
