<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'meta',
        'content',
        'is_published',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_published' => 'boolean',
    ];

    public static function findBySlug(string $slug): ?static
    {
        return static::where('slug', $slug)->where('is_published', true)->first();
    }
}
