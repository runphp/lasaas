<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\TenantAdminPanelProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\ModuleServiceProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    TenantAdminPanelProvider::class,
    FortifyServiceProvider::class,
    ModuleServiceProvider::class,
    TenancyServiceProvider::class,
];
