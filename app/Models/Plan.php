<?php

namespace App\Models;

use App\Enums\BillingCycle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'badge',
        'price',
        'original_price',
        'billing_cycle',
        'features',
        'sort_order',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'billing_cycle' => BillingCycle::class,
            'features' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * 是否免费套餐
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * 是否存在划线价
     */
    public function hasDiscount(): bool
    {
        return $this->original_price && $this->original_price > $this->price;
    }

    /**
     * 获取某个功能限制值
     */
    public function feature(string $key, mixed $default = null): mixed
    {
        return $this->features[$key] ?? $default;
    }

    /**
     * scope: 已启用
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * scope: 推荐的
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
