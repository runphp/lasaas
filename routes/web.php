<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use LaravelLang\Locales\Facades\Locales;
use LaravelLang\Routes\Middlewares\LocalizationByParameter;

$installedLocales = Locales::installed()->pluck('code')->all();
foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () use ($installedLocales) {
        Route::view('/{locale?}', 'welcome')
            ->name('home')
            ->whereIn('locale', $installedLocales)
            ->middleware(LocalizationByParameter::class);

        Route::prefix('{current_team}')
            ->middleware(['auth', 'verified', EnsureTeamMembership::class])
            ->group(function () {
                Route::view('dashboard', 'dashboard')->name('dashboard');
            });

        Route::middleware(['auth'])->group(function () {
            Route::livewire('invitations/{invitation}/accept', 'pages::teams.accept-invitation')->name('invitations.accept');
        });

        require __DIR__ . '/settings.php';
    });
}


