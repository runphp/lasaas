<?php

namespace App\Models;

use App\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id', 'name', 'email', 'phone', 'user_id', 'team_id', 'status', 'expired_at', 'data',
        ];
    }

    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'expires_at' => 'datetime',
            'data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function tenantModules(): HasMany
    {
        return $this->hasMany(TenantModule::class);
    }

    public function getEnabledModules(): array
    {
        return $this->tenantModules()
            ->where('enabled', true)
            ->with('module')
            ->get()
            ->pluck('module.package_name')
            ->toArray();
    }

    public function setModuleEnabled(string $moduleId, bool $enabled): void
    {
        $this->tenantModules()->updateOrCreate(
            ['module_id' => $moduleId],
            ['enabled' => $enabled]
        );
    }
}
