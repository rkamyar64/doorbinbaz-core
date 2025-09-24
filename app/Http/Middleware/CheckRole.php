<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return \App\Http\Libs\Response::error("Unauthenticated", [],401);
        }


        // If user has admin role, allow access to everything
        if ($user->hasRole('ROLE_ADMIN')) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            return \App\Http\Libs\Response::error('You do not have permission to access this resource', [],403);
        }

        return $next($request);
    }
}
