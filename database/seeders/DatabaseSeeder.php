<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 创建或重置 runphp@qq.com 超级管理员用户
        $user = User::updateOrCreate(
            ['email' => 'runphp@qq.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('123456789'),
                'email_verified_at' => now(),
            ]
        );

        // 确保 super_admin 角色存在并分配
        Role::firstOrCreate(['name' => 'super_admin']);
        $user->assignRole('super_admin');

        User::factory(10)->create();

        $this->call([
            PlanSeeder::class,
            PageSeeder::class,
        ]);
    }
}
