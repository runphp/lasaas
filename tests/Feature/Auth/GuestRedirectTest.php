<?php

use App\Models\User;

test('authenticated users are redirected to their current team dashboard on login', function () {
    $user = User::factory()->create();
    $team = $user->personalTeam();

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect("/{$team->slug}/dashboard");
});

test('authenticated users are redirected to their current team dashboard on register', function () {
    $user = User::factory()->create();
    $team = $user->personalTeam();

    $response = $this->actingAs($user)->get(route('register'));

    $response->assertRedirect("/{$team->slug}/dashboard");
});
