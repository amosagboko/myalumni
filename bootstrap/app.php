<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('content:cleanup')->daily();
        $schedule->command('chat:cleanup')->daily();
        $schedule->command('eoi:sync-candidate-payments')->everyTwoMinutes();
        $schedule->command('eoi:cleanup-abandoned')->hourly();
        $schedule->command('dues:assign-annual')->dailyAt('00:05');
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('01:30');
        $schedule->command('backup:monitor')->daily()->at('03:00');
    })
    ->withMiddleware(function (Middleware $middleware) {
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\Admin::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

    // $app->routeMiddleware([
    //     'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
    //     // 'role' => \App\Http\Middleware\RoleMiddleware::class,
    //     'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
    // ]);

    

