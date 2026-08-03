<?php

namespace App\Filament\Resources\Tenants\Tables;

use App\Livewire\Actions\ManageTenantModules;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('models.tenant.id'))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('models.tenant.name'))
                    ->searchable(),
                TextColumn::make('domains.domain')
                    ->label(__('models.tenant.domains'))
                    ->listWithLineBreaks()
                    ->bulleted(),
                TextColumn::make('email')
                    ->label(__('models.tenant.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->label(__('models.tenant.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label(__('models.tenant.user'))
                    ->searchable(),
                TextColumn::make('team.name')
                    ->label(__('models.tenant.team'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label(__('models.tenant.status'))
                    ->badge(),
                TextColumn::make('expired_at')
                    ->label(__('models.tenant.expired_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label(__('validation.attributes.created_at'))
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
                Action::make('modules')
                    ->label(__('filament-resources.module.label'))
                    ->icon(Heroicon::Squares2x2)
                    ->modalWidth(Width::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('关闭'))
                    ->schema([
                        EmbeddedTable::make(ManageTenantModules::class),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
