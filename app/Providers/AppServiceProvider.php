<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        User::observe(UserObserver::class);
        
        // Register Livewire components
        Livewire::component('admin.manage-users', \App\Livewire\Admin\ManageUsers::class);
        Livewire::component('admin.dashboard', \App\Livewire\Admin\Dashboard::class);

        Schema::defaultStringLength(191);
        
        // Increase execution time limit for large imports
        set_time_limit(300); // 5 minutes
        ini_set('memory_limit', '256M');
    }
}
