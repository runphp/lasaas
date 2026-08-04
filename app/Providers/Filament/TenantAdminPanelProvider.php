<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Teams\TeamResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\TenantAdmin\Pages\Dashboard;
use App\Module\ModuleManager;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Contracts\Plugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class TenantAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenant-admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/tenant-admin/theme.css')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/TenantAdmin/Resources'), for: 'App\Filament\TenantAdmin\Resources')
            ->resources([
                UserResource::class,
                RoleResource::class,
                PageResource::class,
                TeamResource::class,
            ])
            ->discoverPages(in: app_path('Filament/TenantAdmin/Pages'), for: 'App\Filament\TenantAdmin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/TenantAdmin/Widgets'), for: 'App\Filament\TenantAdmin\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                'localization.model',
                // ========== Stancl租户中间件【必须放在此处】 ==========
                PreventAccessFromCentralDomains::class,
                InitializeTenancyByDomainOrSubdomain::class,
                // ====================================================
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugins([
                FilamentShieldPlugin::make(),
                ...static::getModulePlugins(),
            ]);
    }

    /**
     * 从支持 tenant 区域的模块中收集租户 admin 面板插件实例。
     *
     * 约定（约定大于配置）：模块实现 TenantAdminPanelPlugin 接口的类即自动注册，
     * 无需在 composer.json 中声明。
     *
     * @return array<Plugin>
     */
    protected static function getModulePlugins(): array
    {
        /** @var ModuleManager $manager */
        $manager = app(ModuleManager::class);

        $plugins = [];

        foreach ($manager->getTenantAdminPanelPlugins() as $pluginClass) {
            $plugins[] = app($pluginClass);
        }

        return $plugins;
    }
}
