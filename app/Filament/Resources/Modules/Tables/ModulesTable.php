<?php

namespace App\Filament\Resources\Modules\Tables;

use App\Enums\ModuleStatus;
use App\Models\Module;
use App\Models\TenantModule;
use App\Module\ModuleManager;
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
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('version')
                    ->searchable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('status')
                    ->onColor('success')
                    ->offColor('gray')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn ($record) => $record->status === ModuleStatus::ACTIVE)
                    ->updateStateUsing(function ($record, $state) {
                        $manager = app(ModuleManager::class);

                        if ($state) {
                            $manager->enable($record);
                        } else {
                            $manager->disable($record);
                        }
                    })
                    ->afterStateUpdated(function ($record, $state) {
                        $label = $state ? '启用' : '禁用';
                        Notification::make()
                            ->title("模块 {$record->package_name} 已{$label}")
                            ->success()
                            ->send();
                    }),
                TextColumn::make('installed_at')
                    ->dateTime()
                    ->placeholder('未安装')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
                    ->visible(fn (Module $record): bool => ! empty(app(ModuleManager::class)->platformSettingsSchema($record)))
                    ->schema(fn (Module $record): array => app(ModuleManager::class)->platformSettingsSchema($record))
                    ->fillForm(fn (Module $record): array => app(ModuleManager::class)->resolvePlatformSettings($record)?->toArray() ?? [])
                    ->action(function (Module $record, array $data): void {
                        app(ModuleManager::class)->resolvePlatformSettings($record)?->fill($data)?->save();

                        Notification::make()->success()->title(__('filament-resources.module.settings.saved'))->send();
                    }),
                Action::make('uninstall')
                    ->authorize('uninstall')
                    ->label(__('filament-resources.module.uninstall.label'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription(function ($record) {
                        $name = $record->name;
                        $pkg = $record->package_name;
                        $installed = $record->isInstalled() ? __('filament-resources.module.uninstall.modal.actions.uninstall.label').' ' : '';

                        return __('filament-resources.module.uninstall.modal.heading', ['label' => $name]);
                    })
                    ->action(function ($record) {
                        $hasEnabledTenant = TenantModule::where('module_id', $record->id)
                            ->where('enabled', true)
                            ->exists();

                        // 预校验：存在启用该模块的租户
                        if ($hasEnabledTenant) {
                            Notification::make()
                                ->title(__('filament-resources.module.uninstall.modal.heading'))
                                ->body(__('filament-resources.module.uninstall.modal.description'))
                                ->danger()
                                ->send();

                            return;
                        }
                        $manager = app(ModuleManager::class);
                        $manager->uninstall($record);

                        Notification::make()
                            ->title("模块 {$record->package_name} 已卸载")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
