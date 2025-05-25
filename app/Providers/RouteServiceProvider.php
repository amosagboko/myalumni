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
     * Get the appropriate home route based on user role.
     */
    public static function getHomeRoute(): string
    {
        if (!auth()->check()) {
            return self::HOME;
        }

        $user = auth()->user();
        
        if ($user->hasRole('administrator')) {
            return 'admin.dashboard';
        }
        
        if ($user->hasRole('alumni-relations-officer')) {
            return 'alumni-relations-officer.home';
        }
        
        if ($user->hasRole('alumni')) {
            return 'alumni.home';
        }
        
        return self::HOME;
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