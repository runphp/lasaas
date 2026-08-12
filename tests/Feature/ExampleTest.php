<?php

use Crumbls\Layup\Models\Page;

test('returns a successful response', function () {
    Page::create([
        'slug' => 'home',
        'path' => 'home',
        'title' => 'Home',
        'status' => Page::STATUS_PUBLISHED,
        'content' => ['rows' => []],
    ]);

    $this->get(route('home'))->assertOk();
});
