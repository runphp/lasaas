<?php

declare(strict_types=1);

namespace App\Livewire\Actions;

use App\Models\Module;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Module\ModuleBootLoader;
use App\Module\ModuleDiscoveryManager;
use App\Module\ModuleSettingManager;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ManageTenantModules extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?Tenant $record = null;

    public function mount(Tenant $record): void
    {
        $this->record = $record;
    }

    public function render(): View
    {
        return view('livewire.actions.manage-tenant-modules');
    }

    public function getTenant(): Tenant
    {
        return $this->record;
    }

    public function table(Table $table): Table
    {
        $tenant = $this->getTenant();
        $discovery = app(ModuleDiscoveryManager::class);

        $tenantAreaModuleIds = $discovery->discover()
            ->filter(fn (Module $module) => $discovery->supportsArea($module, 'tenant'))
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
                    ->schema(fn (Module $record): array => app(ModuleSettingManager::class)->tenantSettingsSchema($record))
                    ->fillForm(fn (Module $record): array => app(ModuleSettingManager::class)->resolveTenantSettings($record, $tenant)?->toArray() ?? [])
                    ->action(function (Module $record, array $data): void {
                        app(ModuleSettingManager::class)->resolveTenantSettings($record, $this->getTenant())?->fill($data)?->save();

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
        app(ModuleBootLoader::class)->enableForTenant($module, $this->getTenant());

        Notification::make()->success()->title(__('模块已安装并启用'))->send();
    }

    protected function setModuleEnabled(Module $module, bool $enabled): void
    {
        $bootLoader = app(ModuleBootLoader::class);

        if ($enabled) {
            $bootLoader->enableForTenant($module, $this->getTenant());

            Notification::make()->success()->title(__('模块已启用'))->send();

            return;
        }

        $bootLoader->disableForTenant($module, $this->getTenant());

        Notification::make()->success()->title(__('模块已禁用'))->send();
    }

    protected function uninstallModule(Module $module): void
    {
        app(ModuleBootLoader::class)->uninstallForTenant($module, $this->getTenant());

        Notification::make()->success()->title(__('模块已卸载'))->send();
    }
}
