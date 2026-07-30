<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'tenant::settings.profile')->name('tenant.profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/appearance', 'tenant::settings.appearance')->name('tenant.appearance.edit');
    Route::livewire('settings/locale', 'tenant::settings.locale')->name('tenant.locale.edit');
    Route::livewire('settings/security', 'tenant::settings.security')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('tenant.security.edit');

    Route::livewire('settings/teams', 'tenant::teams.index')->name('tenant.teams.index');

    Route::middleware(EnsureTeamMembership::class)->group(function () {
        Route::livewire('settings/teams/{team}', 'tenant::teams.edit')->name('tenant.teams.edit');
    });
});
