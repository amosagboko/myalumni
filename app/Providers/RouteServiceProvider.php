<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = 'dashboard';

    /**
     * Resolve the named route for the authenticated user's home/dashboard.
     */
    public static function getHomeRoute(): string
    {
        if (!auth()->check()) {
            return 'login';
        }

        $user = auth()->user();

        if ($user->hasRole('administrator')) {
            return 'admin.dashboard';
        }

        if ($user->hasRole('elcom-chairman')) {
            return 'elcom-chairman.dashboard';
        }

        if ($user->hasRole('elcom')) {
            return 'elcom.elections.index';
        }

        if ($user->hasRole('alumni-relations-officer')) {
            return 'alumni-relations-officer.home';
        }

        if ($user->hasRole('student-affairs')) {
            return 'student-affairs.home';
        }

        if ($user->hasRole('academic-affairs')) {
            return 'academic-affairs.home';
        }

        if ($user->hasRole('alumni-agent')) {
            return 'agent.dashboard';
        }

        if ($user->hasRole('alumni')) {
            return 'alumni.home';
        }

        // Legacy accounts: linked alumni record but missing Spatie role assignment.
        if ($user->alumni) {
            return 'alumni.home';
        }

        return 'login';
    }

    /**
     * Redirect authenticated users to the correct home after login or legacy /dashboard visits.
     */
    public static function redirectToHome(): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        if ($user && $user->hasRole('alumni') && $user->is_first_login) {
            return redirect()->route('alumni.onboarding');
        }

        return redirect()->intended(route(self::getHomeRoute(), absolute: false));
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
} 