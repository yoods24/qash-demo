<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToTenant
{
    /**
     * Ensure the authenticated user belongs to the current tenant context.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If not authenticated, let the auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        $currentTenantId = tenant('id');
        if ($currentTenantId && $user->tenant_id !== $currentTenantId) {
            abort(403, 'You are not allowed to access this tenant.');
        }

        return $next($request);
    }
}

