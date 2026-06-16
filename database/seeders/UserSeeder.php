<?php

namespace Database\Seeders;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 创建或重置 runphp@qq.com 超级管理员用户
        $user = User::updateOrCreate(
            ['email' => 'runphp@qq.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]
        );

        // 确保 super_admin 角色存在并分配
        Role::firstOrCreate(['name' => 'super_admin']);
        $user->assignRole('super_admin');

        // 确保超级管理员有个人团队（与 UserFactory 行为一致）
        if (! $user->personalTeam()) {
            $team = Team::factory()->personal()->create([
                'name' => $user->name."'s Team",
                'slug' => null,
            ]);

            $team->members()->attach($user, [
                'role' => TeamRole::Owner->value,
            ]);

            $user->switchTeam($team);
        }

        User::factory(10)->create();
    }
}
