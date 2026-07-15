<?php

namespace App\Providers;

use App\Services\PortalModeService;
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
        return app(PortalModeService::class)->resolveHomeRoute(auth()->user(), request());
    }

    /**
     * Redirect authenticated users to the correct home after login or legacy /dashboard visits.
     */
    public static function redirectToHome(): \Illuminate\Http\RedirectResponse
    {
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