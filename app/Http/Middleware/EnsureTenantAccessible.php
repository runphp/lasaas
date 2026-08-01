<?php

namespace App\Http\Middleware;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class EnsureTenantAccessible
{
    /**
     * Block requests to tenants that are not active or whose database cannot be reached.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! tenancy()->initialized) {
            return $next($request);
        }

        $tenant = tenancy()->tenant;

        if ($tenant === null) {
            throw new HttpException(404);
        }

        if ($tenant->status !== TenantStatus::ACTIVE) {
            return $this->statusUnavailableResponse($tenant);
        }

        $failure = $this->findTenantDatabaseFailure($tenant);

        if ($failure !== null) {
            return $this->databaseUnavailableResponse($tenant, $failure);
        }

        return $next($request);
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
