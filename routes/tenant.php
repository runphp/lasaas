<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelLang\Locales\Facades\Locales;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/
$installedLocales = Locales::installed()->pluck('code')->all();
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () use ($installedLocales) {
    Route::livewire('/{locale?}', 'tenant::home')
        ->name('tenant.home')
        ->whereIn('locale', $installedLocales)
        ->middleware('localization.home');
});
