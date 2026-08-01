<?php

namespace App\Models;

use App\Enums\ModuleStatus;
use App\Enums\TenantStatus;
use App\Tenancy\TenantDatabaseConfig;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\DatabaseConfig;

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

    public function tenantDatabase(): HasOne
    {
        return $this->hasOne(TenantDatabase::class);
    }

    public function database(): DatabaseConfig
    {
        return new TenantDatabaseConfig($this);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function setDatabaseConnection(array $config): static
    {
        $this->tenantDatabase()->updateOrCreate([], $config);

        return $this;
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function tenantModules(): HasMany
    {
        return $this->hasMany(TenantModule::class);
    }

    /**
     * 获取租户当前启用的模块包名列表。
     *
     * 仅返回中央应用仍处于 active 状态的模块——中央禁用后，
     * 即使租户侧 tenant_modules.enabled 仍为 true，也不视为启用。
     */
    public function getEnabledModules(): array
    {
        return $this->tenantModules()
            ->where('enabled', true)
            ->whereHas('module', fn ($query) => $query->where('status', ModuleStatus::ACTIVE))
            ->with('module')
            ->get()
            ->pluck('module.package_name')
            ->toArray();
    }

    public function setModuleEnabled(int|string $moduleId, bool $enabled): void
    {
        $this->tenantModules()->updateOrCreate(
            ['module_id' => $moduleId],
            ['enabled' => $enabled]
        );
    }
}
