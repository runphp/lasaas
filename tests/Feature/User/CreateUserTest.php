<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('admin creating a user also creates their personal team', function () {
    $admin = User::factory()->create();

    $role = Role::create(['name' => 'admin']);
    $role->givePermissionTo(Permission::create(['name' => 'ViewAny:User']));
    $role->givePermissionTo(Permission::create(['name' => 'Create:User']));
    $admin->assignRole($role);

    test()->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New Admin User',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'newadmin@example.com')->firstOrFail();

    expect($user->teams()->count())->toBe(1);
    expect($user->personalTeam())->not->toBeNull();
    expect($user->current_team_id)->toBe($user->personalTeam()->id);
    expect($user->ownsTeam($user->personalTeam()))->toBeTrue();
});
