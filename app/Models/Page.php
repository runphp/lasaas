<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'title', 'meta', 'content', 'is_published'])]
class Page extends Model
{
    protected $casts = [
        'meta' => 'array',
        'is_published' => 'boolean',
    ];

    public static function findBySlug(string $slug): ?static
    {
        return static::where('slug', $slug)->where('is_published', true)->first();
    }
}
