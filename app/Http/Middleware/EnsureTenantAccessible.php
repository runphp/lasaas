<?php

namespace App\Http\Middleware;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        if ($this->tenantDatabaseIsUnreachable($tenant)) {
            return response()
                ->view('tenant.unavailable', [
                    'title' => __('tenant.unavailable.title'),
                    'message' => __('tenant.unavailable.database'),
                    'retry' => true,
                ], 503);
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

    protected function tenantDatabaseIsUnreachable(Tenant $tenant): bool
    {
        try {
            if (! $this->tenantDatabaseExists($tenant)) {
                return true;
            }

            DB::connection()->getPdo();
        } catch (Throwable) {
            return true;
        }

        return false;
    }

    protected function tenantDatabaseExists(Tenant $tenant): bool
    {
        $name = $tenant->database()->getName();

        return $name !== null && $tenant->database()->manager()->databaseExists($name);
    }
}
