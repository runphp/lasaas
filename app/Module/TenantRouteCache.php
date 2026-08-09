<?php

declare(strict_types=1);

namespace App\Module;

use App\Models\Tenant;
use Illuminate\Filesystem\Filesystem;

/**
 * 域名专属路由缓存 —— 1 份中央应用缓存 + N 份租户应用缓存。
 *
 * 缓存目录：bootstrap/cache/tenant-routes/
 * - central.php                     中央应用路由集合
 * - tenant_{sha1(tenantKey)}.php    每个租户的应用路由集合
 *
 * 缓存文件由 `tenancy:routes-cache` 生成，`tenancy:routes-clear` 清除。
 * 模块生命周期操作（启用/禁用/卸载）后须调用 flushTenant()/flushAll() 使缓存失效。
 */
class TenantRouteCache
{
    public static function directory(): string
    {
        return base_path('bootstrap/cache/tenant-routes');
    }

    public static function fileFor(string $key): string
    {
        return self::directory().'/'.$key.'.php';
    }

    public static function tenantKey(Tenant $tenant): string
    {
        return 'tenant_'.sha1($tenant->getTenantKey());
    }

    /**
     * 创建目录并清空，供缓存命令全量重建。
     */
    public static function prepareDirectory(): void
    {
        $files = new Filesystem;

        $files->ensureDirectoryExists(self::directory());
        $files->cleanDirectory(self::directory());
    }

    /**
     * 使单个租户的缓存失效（租户级模块启停/卸载后调用）。
     */
    public static function flushTenant(Tenant $tenant): void
    {
        (new Filesystem)->delete(self::fileFor(self::tenantKey($tenant)));
    }

    /**
     * 使全部缓存失效（全局模块启停/卸载后调用）。
     */
    public static function flushAll(): void
    {
        (new Filesystem)->deleteDirectory(self::directory());
    }
}
