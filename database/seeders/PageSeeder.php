<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::query()->create([
            'slug' => 'home',
            'title' => 'Welcome to Lasaas',
            'is_published' => true,
        ]);
    }
}
