<?php

use App\Models\User;
use BezhanSalleh\LanguageSwitch\Events\LocaleChanged;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

test('admin panel renders the language switch', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::create(['name' => 'admin']));

    actingAs($user);

    $this->get('/admin')
        ->assertOk()
        ->assertSee('language-switch-component');
});

test('changing the locale persists to the users locale column', function () {
    $user = User::factory()->create();

    actingAs($user);

    event(new LocaleChanged('zh-CN'));

    expect($user->fresh()->locale)->toBe('zh-CN');
});
