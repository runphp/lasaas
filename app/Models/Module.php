<?php

namespace App\Models;

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
    ];

    protected function casts(): array
    {
        return [
            'dependencies' => 'array',
            'after'        => 'array',
            'areas'        => 'array',
        ];
    }

    public function tenantModules(): HasMany
    {
        return $this->hasMany(TenantModule::class);
    }
}
