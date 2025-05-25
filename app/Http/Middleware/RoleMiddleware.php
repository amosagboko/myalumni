<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {

        $user = $request->user();

        // Debugging: Check if the user exists
        if (!$user) {
            abort(403, 'Unauthorized - No User');
        }

        // Debugging: Check the roles assigned to the user
        if (!$user->hasRole($role)) {
            abort(403, 'Unauthorized - You do not have the required role. Your roles: ' . implode(', ', $user->getRoleNames()->toArray()));
        }

        return $next($request);
    }
}
