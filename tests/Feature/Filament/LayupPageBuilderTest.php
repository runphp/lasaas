<?php

use App\Models\User;
use Crumbls\Layup\Models\Page;
use Crumbls\Layup\Resources\PageResource\Pages\EditPage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $user = User::factory()->create();

    $role = Role::firstOrCreate(['name' => 'super_admin']);

    foreach (['ViewAny:Page', 'View:Page', 'Update:Page', 'Create:Page'] as $name) {
        $role->givePermissionTo(Permission::firstOrCreate(['name' => $name]));
    }

    $user->assignRole($role);

    actingAs($user);
});

test('layup pages resource exposes the visual builder for editing', function () {
    $page = Page::create([
        'path' => 'home',
        'slug' => 'home',
        'title' => 'Home',
        'status' => Page::STATUS_PUBLISHED,
        'content' => [],
    ]);

    Livewire::test(EditPage::class, ['record' => $page->id])
        ->assertOk()
        ->assertFormFieldExists('content')
        ->assertHasNoFormErrors();
});

test('layup builder persists edited widget data', function () {
    $page = Page::create([
        'path' => 'features',
        'slug' => 'features',
        'title' => 'Features',
        'status' => Page::STATUS_PUBLISHED,
        'content' => [],
    ]);

    Livewire::test(EditPage::class, ['record' => $page->id])
        ->fillForm([
            'content' => [
                'rows' => [
                    [
                        'id' => 'row_hero',
                        'settings' => ['gap' => 'gap-6'],
                        'columns' => [
                            [
                                'id' => 'col_hero',
                                'span' => ['sm' => 12, 'md' => 12, 'lg' => 12, 'xl' => 12],
                                'settings' => ['padding' => 'p-4'],
                                'widgets' => [
                                    [
                                        'id' => 'widget_landing_hero_1',
                                        'type' => 'landing-hero',
                                        'data' => [
                                            'heading_line1' => 'Edited via builder',
                                            'description' => 'Saved through the visual editor',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $fresh = $page->fresh();

    expect($fresh->content['rows'][0]['columns'][0]['widgets'][0]['type'])->toBe('landing-hero')
        ->and($fresh->content['rows'][0]['columns'][0]['widgets'][0]['data']['heading_line1'])->toBe('Edited via builder');
});
