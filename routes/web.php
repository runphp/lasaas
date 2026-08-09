<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use LaravelLang\Locales\Facades\Locales;

$installedLocales = Locales::installed()->pluck('code')->all();
Route::livewire('/{locale?}', 'pages::home')
    ->name('home')
    ->whereIn('locale', $installedLocales)
    ->middleware('localization.home');

Route::livewire('/pricing/{locale?}', 'pages::pricing')
    ->name('pricing')
    ->whereIn('locale', $installedLocales)
    ->middleware('localization.home');

Route::livewire('/features/{locale?}', 'pages::features')
    ->name('features')
    ->whereIn('locale', $installedLocales)
    ->middleware('localization.home');

Route::livewire('/about/{locale?}', 'pages::about')
    ->name('about')
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
