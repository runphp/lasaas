<?php

use App\Http\Middleware\EnsureTeamMembership;
use Crumbls\Layup\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use LaravelLang\Locales\Facades\Locales;

$installedLocales = Locales::installed()->pluck('code')->all();
Route::get('/{locale?}', PageController::class)
    ->name('home')
    ->defaults('slug', 'home')
    ->whereIn('locale', $installedLocales)
    ->middleware('localization.home');

Route::get('/pricing/{locale?}', PageController::class)
    ->name('pricing')
    ->defaults('slug', 'pricing')
    ->whereIn('locale', $installedLocales)
    ->middleware('localization.home');

Route::get('/features/{locale?}', PageController::class)
    ->name('features')
    ->defaults('slug', 'features')
    ->whereIn('locale', $installedLocales)
    ->middleware('localization.home');

Route::get('/about/{locale?}', PageController::class)
    ->name('about')
    ->defaults('slug', 'about')
    ->whereIn('locale', $installedLocales)
    ->middleware('localization.home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class, 'localization.session'])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::livewire('invitations/{invitation}/accept', 'pages::teams.accept-invitation')->name('invitations.accept');
});
Route::middleware(['localization.session', 'localization.model'])->group(function () {
    require __DIR__.'/settings.php';
});
