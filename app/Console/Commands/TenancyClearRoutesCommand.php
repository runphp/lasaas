<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Module\TenantRouteCache;
use Illuminate\Console\Command;

/**
 * 清除按域名分发的路由缓存（bootstrap/cache/tenant-routes/）。
 */
class TenancyClearRoutesCommand extends Command
{
    protected $signature = 'tenancy:routes-clear';

    protected $description = '清除按域名分发的路由缓存';

    public function handle(): int
    {
        TenantRouteCache::flushAll();

        // 标准 route:cache 缓存会绕过按域名分发，一并清除
        $this->callSilently('route:clear');

        $this->components->info('Tenant route caches cleared.');

        return self::SUCCESS;
    }
}
