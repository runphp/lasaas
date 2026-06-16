<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Welcome to Lasaas',
                'is_published' => true,
            ]
        );

        Page::query()->updateOrCreate(
            ['slug' => 'features'],
            [
                'title' => 'Features',
                'is_published' => true,
            ]
        );

        Page::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'About',
                'is_published' => true,
            ]
        );

        Page::query()->updateOrCreate(
            ['slug' => 'pricing'],
            [
                'title' => 'Pricing',
                'is_published' => true,
            ]
        );
    }
}
