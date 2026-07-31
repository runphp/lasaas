<?php

namespace App\Models;

use App\Enums\ModuleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'package_name',
        'name',
        'description',
        'version',
        'provider_class',
        'weight',
        'dependencies',
        'after',
        'areas',
        'path',
        'status',
        'installed_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'dependencies' => 'array',
            'after' => 'array',
            'areas' => 'array',
            'status' => ModuleStatus::class,
            'installed_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    /**
     * 判断模块是否已安装（曾被启用过）。
     */
    public function isInstalled(): bool
    {
        return $this->installed_at !== null;
    }

    public function tenantModules(): HasMany
    {
        return $this->hasMany(TenantModule::class);
    }
}
