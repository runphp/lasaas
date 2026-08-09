<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * 租户可用性校验服务 —— 返回阻塞响应或 null（可继续处理）。
 *
 * 校验租户状态与数据库可达性，供 EnsureTenantAccessible 中间件
 * 与 InitializeTenantAndDispatchRoutes 分发中间件共用，避免复制逻辑。
 */
class TenantAvailability
{
    /**
     * @return Response|null 租户不可用时返回阻塞响应；可用时返回 null
     */
    public function check(Tenant $tenant): ?Response
    {
        if ($tenant->status !== TenantStatus::ACTIVE) {
            return $this->statusUnavailableResponse($tenant);
        }

        $failure = $this->findTenantDatabaseFailure($tenant);

        if ($failure !== null) {
            return $this->databaseUnavailableResponse($tenant, $failure);
        }

        return null;
    }

    protected function statusUnavailableResponse(Tenant $tenant): Response
    {
        $message = match ($tenant->status) {
            TenantStatus::PENDING => __('tenant.unavailable.pending'),
            TenantStatus::SUSPENDED => __('tenant.unavailable.suspended'),
            TenantStatus::EXPIRED => __('tenant.unavailable.expired'),
            TenantStatus::DISABLED => __('tenant.unavailable.disabled'),
            default => __('tenant.unavailable.unknown'),
        };

        return response()
            ->view('tenant.unavailable', [
                'title' => __('tenant.unavailable.title'),
                'message' => $message,
            ], 403);
    }

    protected function databaseUnavailableResponse(Tenant $tenant, Throwable $failure): Response
    {
        Log::warning('Tenant database unreachable', [
            'tenant_id' => $tenant->getTenantKey(),
            'database' => $tenant->database()->getName(),
            'exception' => $failure,
        ]);

        $message = __('tenant.unavailable.database');

        if (app()->environment('local')) {
            $message .= ' '.$failure->getMessage();
        }

        return response()
            ->view('tenant.unavailable', [
                'title' => __('tenant.unavailable.title'),
                'message' => $message,
                'retry' => true,
            ], 503);
    }

    /**
     * Returns the underlying reason the tenant database is unreachable, or null when it is reachable.
     */
    protected function findTenantDatabaseFailure(Tenant $tenant): ?Throwable
    {
        $database = $tenant->database();

        try {
            if ($database->getName() === null) {
                return new RuntimeException('tenant_databases 表没有该租户的数据库配置记录。');
            }

            // Bootstrap 失败时租户连接不会被配置，这里补充构建以便拿到真实的错误原因。
            if (config('database.connections.tenant') === null) {
                config()->set('database.connections.tenant', $database->connection());
            }

            DB::connection('tenant')->getPdo();
        } catch (Throwable $exception) {
            return $exception;
        }

        return null;
    }
}
