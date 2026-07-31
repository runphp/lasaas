<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Module;
use App\Models\TenantModule;
use App\Module\ModuleManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ManageTenantModules extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = TenantResource::class;

    protected string $view = 'filament.resources.tenants.pages.manage-tenant-modules';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->mountCanAuthorizeAccess();
    }

    public function table(Table $table): Table
    {
        $tenant = $this->getRecord();
        $manager = app(ModuleManager::class);

        $tenantAreaModuleIds = $manager->discover()
            ->filter(fn (Module $module) => $manager->supportsArea($module, 'tenant'))
            ->pluck('id');

        return $table
            ->query(
                Module::query()
                    ->whereIn('id', $tenantAreaModuleIds)
                    ->with(['tenantModules' => fn ($query) => $query->where('tenant_id', $tenant->id)])
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('package_name')
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('安装状态'))
                    ->badge()
                    ->state(fn (Module $record): string => $this->statusLabel($record))
                    ->color(fn (Module $record): string => $this->statusColor($record)),
            ])
            ->actions([
                Action::make('install')
                    ->label(__('安装并启用'))
                    ->visible(fn (Module $record): bool => $this->tenantModuleFor($record) === null)
                    ->action(fn (Module $record) => $this->installModule($record)),
                Action::make('enable')
                    ->label(__('启用'))
                    ->visible(fn (Module $record): bool => ($tenantModule = $this->tenantModuleFor($record)) !== null && ! $tenantModule->enabled)
                    ->action(fn (Module $record) => $this->setModuleEnabled($record, true)),
                Action::make('disable')
                    ->label(__('禁用'))
                    ->visible(fn (Module $record): bool => ($tenantModule = $this->tenantModuleFor($record)) !== null && $tenantModule->enabled)
                    ->action(fn (Module $record) => $this->setModuleEnabled($record, false)),
                Action::make('settings')
                    ->label(__('设置'))
                    ->icon(Heroicon::Cog6Tooth)
                    ->visible(fn (Module $record): bool => $this->tenantModuleFor($record) !== null)
                    ->schema(fn (Module $record): array => $manager->tenantSettingsSchema($record))
                    ->fillForm(fn (Module $record): array => $this->tenantModuleFor($record)?->settings ?? [])
                    ->action(function (Module $record, array $data): void {
                        $this->tenantModuleFor($record)?->update(['settings' => $data]);

                        Notification::make()->success()->title(__('已保存'))->send();
                    }),
                Action::make('uninstall')
                    ->label(__('卸载'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Module $record): bool => $this->tenantModuleFor($record) !== null)
                    ->action(fn (Module $record) => $this->uninstallModule($record)),
            ])
            ->paginated(false);
    }

    protected function tenantModuleFor(Module $module): ?TenantModule
    {
        return $module->tenantModules->first();
    }

    protected function statusLabel(Module $record): string
    {
        $tenantModule = $this->tenantModuleFor($record);

        if ($tenantModule === null) {
            return __('未安装');
        }

        return $tenantModule->enabled ? __('已启用') : __('已禁用');
    }

    protected function statusColor(Module $record): string
    {
        $tenantModule = $this->tenantModuleFor($record);

        if ($tenantModule === null) {
            return 'gray';
        }

        return $tenantModule->enabled ? 'success' : 'warning';
    }

    protected function installModule(Module $module): void
    {
        app(ModuleManager::class)->enableForTenant($module, $this->getRecord());

        Notification::make()->success()->title(__('模块已安装并启用'))->send();
    }

    protected function setModuleEnabled(Module $module, bool $enabled): void
    {
        $manager = app(ModuleManager::class);

        if ($enabled) {
            $manager->enableForTenant($module, $this->getRecord());

            Notification::make()->success()->title(__('模块已启用'))->send();

            return;
        }

        $manager->disableForTenant($module, $this->getRecord());

        Notification::make()->success()->title(__('模块已禁用'))->send();
    }

    protected function uninstallModule(Module $module): void
    {
        app(ModuleManager::class)->uninstallForTenant($module, $this->getRecord());

        Notification::make()->success()->title(__('模块已卸载'))->send();
    }
}
