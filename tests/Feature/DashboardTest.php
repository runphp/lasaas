<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

test('guests are redirected to the login page', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});

test('central dashboard route is registered with the web middleware group', function () {
    $route = Route::getRoutes()->getByName('dashboard');

    expect($route->gatherMiddleware())
        ->toContain('web');
});

test('authenticated users can visit the dashboard via a real session', function () {
    $user = User::factory()->create();
    $team = $user->personalTeam();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->get("/{$team->slug}/dashboard")
        ->assertOk()
        ->assertViewIs('dashboard');
});
