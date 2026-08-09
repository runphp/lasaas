<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantAvailability;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EnsureTenantAccessible
{
    public function __construct(
        protected TenantAvailability $availability,
    ) {}

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

        return $this->availability->check($tenant) ?? $next($request);
    }
}
