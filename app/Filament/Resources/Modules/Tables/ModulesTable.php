<?php

namespace App\Filament\Resources\Modules\Tables;

use App\Enums\ModuleStatus;
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
                Action::make('uninstall')
                    ->label('卸载')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription(function ($record) {
                        $name = $record->name;
                        $pkg = $record->package_name;
                        $installed = $record->isInstalled() ? '（将执行 uninstall 钩子）' : '';

                        return "确定要卸载模块 \"{$name}\" ({$pkg}) 吗？{$installed}";
                    })
                    ->action(function ($record) {
                        $hasEnabledTenant = TenantModule::where('module_id', $record->id)
                            ->where('enabled', true)
                            ->exists();

                        // 预校验：存在启用该模块的租户
                        if ($hasEnabledTenant) {
                            Notification::make()
                                ->title('无法卸载模块')
                                ->body('仍有租户正在启用此模块，请先在所有租户内禁用/卸载该模块后重试。')
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
