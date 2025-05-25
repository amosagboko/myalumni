<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // If user is an admin, redirect to admin dashboard
                if ($user->hasRole('administrator')) {
                    return redirect()->route('admin.dashboard');
                }

                // If user is an alumni (including those who are also agents), redirect to alumni home
                if ($user->hasRole('alumni')) {
                    // If it's their first login, redirect to onboarding
                    if ($user->is_first_login) {
                        return redirect()->route('alumni.onboarding');
                    }
                    // Otherwise redirect to alumni home
                    return redirect()->route('alumni.home');
                }

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
} 