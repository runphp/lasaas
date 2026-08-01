<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class TenantDatabase extends Model
{
    protected $table = 'tenant_databases';

    protected $primaryKey = 'tenant_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saved(function (TenantDatabase $tenantDatabase) {
            if ($tenantDatabase->wasRecentlyCreated) {
                return;
            }

            if ($tenantDatabase->wasChanged(['connection', 'database', 'prefix', 'prefix_indexes'])) {
                MigrateDatabase::dispatch($tenantDatabase->tenant);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'prefix_indexes' => 'boolean',
            'strict' => 'boolean',
            'options' => 'array',
            'password' => 'encrypted',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
