<?php

namespace App\Providers;

use App\Models\Election;
use App\Policies\ElectionPolicy;
use App\Policies\CandidatePolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        Gate::policy(Election::class, ElectionPolicy::class);
        Gate::policy(\App\Models\Candidate::class, CandidatePolicy::class);

        // Register Livewire components
        Livewire::component('admin.manage-users', \App\Livewire\Admin\ManageUsers::class);
        Livewire::component('admin.dashboard', \App\Livewire\Admin\Dashboard::class);
        Livewire::component('admin.assign-categories', \App\Livewire\Admin\AssignCategories::class);
        Livewire::component('alumni.clearance-status', \App\Livewire\Alumni\ClearanceStatus::class);
        Livewire::component('student-affairs.clearance', \App\Livewire\StudentAffairs\Clearance::class);
        Livewire::component('academic-affairs.clearance', \App\Livewire\AcademicAffairs\Clearance::class);
        Livewire::component('admin.clearance-audit', \App\Livewire\Admin\ClearanceAudit::class);
        Livewire::component('student-affairs.dashboard', \App\Livewire\StudentAffairs\Dashboard::class);
        Livewire::component('student-affairs.audit', \App\Livewire\StudentAffairs\Audit::class);
        Livewire::component('academic-affairs.dashboard', \App\Livewire\AcademicAffairs\Dashboard::class);
        Livewire::component('academic-affairs.audit', \App\Livewire\AcademicAffairs\Audit::class);
    }
}
