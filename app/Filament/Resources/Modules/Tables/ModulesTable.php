<?php

namespace App\Filament\Resources\Modules\Tables;

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Models\TenantModule;
use App\Module\ModuleBootLoader;
use App\Module\ModuleSettingManager;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ModulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('package_name')
                    ->label(__('models.module.package_name'))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('models.module.name'))
                    ->searchable(),
                TextColumn::make('version')
                    ->label(__('models.module.version'))
                    ->searchable(),
                TextColumn::make('weight')
                    ->label(__('models.module.weight'))
                    ->sortable()->numeric(),
                ToggleColumn::make('status')
                    ->label(__('models.module.status'))
                    ->onColor('success')
                    ->offColor('gray')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn ($record) => $record->status === ModuleStatus::ACTIVE)
                    ->updateStateUsing(function ($record, $state) {
                        $bootLoader = app(ModuleBootLoader::class);

                        if ($state) {
                            $bootLoader->enable($record);
                        } else {
                            $bootLoader->disable($record);
                        }
                    })
                    ->afterStateUpdated(function ($record, $state) {
                        $statusText = $state
                            ? __('models.module.statuses.active')
                            : __('models.module.statuses.inactive');

                        Notification::make()
                            ->title(__('filament-resources.module.notify.toggle_title', [
                                'pkg' => $record->package_name,
                                'status' => $statusText,
                            ]))
                            ->success()
                            ->send();
                    }),
                TextColumn::make('installed_at')
                    ->label(__('models.module.installed_at'))
                    ->dateTime()
                    ->placeholder(__('models.module.placeholders.installed_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('settings')
                    ->authorize('settings')
                    ->label(__('filament-resources.module.settings.label'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->visible(fn (Module $record): bool => ! empty(app(ModuleSettingManager::class)->platformSettingsSchema($record)))
                    ->schema(fn (Module $record): array => app(ModuleSettingManager::class)->platformSettingsSchema($record))
                    ->fillForm(fn (Module $record): array => app(ModuleSettingManager::class)->resolvePlatformSettings($record)?->toArray() ?? [])
                    ->action(function (Module $record, array $data): void {
                        app(ModuleSettingManager::class)->resolvePlatformSettings($record)?->fill($data)?->save();

                        Notification::make()->success()->title(__('filament-resources.module.settings.saved'))->send();
                    }),
                Action::make('uninstall')
                    ->authorize('uninstall')
                    ->label(__('filament-resources.module.uninstall.label'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => __('filament-resources.module.uninstall.modal.heading', ['label' => $record->name]))
                    ->modalDescription(__('filament-resources.module.uninstall.modal.description'))
                    ->modalSubmitActionLabel(__('filament-resources.module.uninstall.modal.actions.uninstall.label'))
                    ->modalCancelActionLabel(__('filament-resources.module.uninstall.modal.actions.cancel.label'))
                    ->action(function ($record) {
                        $hasEnabledTenant = TenantModule::where('module_id', $record->id)
                            ->where('enabled', true)
                            ->exists();

                        // 预校验：存在启用该模块的租户
                        if ($hasEnabledTenant) {
                            Notification::make()
                                ->title(__('filament-resources.module.uninstall.notify.fail'))
                                ->body(__('filament-resources.module.uninstall.notify.fail_body'))
                                ->danger()
                                ->send();

                            return;
                        }
                        $bootLoader = app(ModuleBootLoader::class);
                        $bootLoader->uninstall($record);

                        Notification::make()
                            ->title(__('filament-resources.module.uninstall.notify.success', [
                                'pkg' => $record->package_name,
                            ]))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
