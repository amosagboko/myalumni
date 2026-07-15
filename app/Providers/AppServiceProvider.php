<?php

namespace App\Providers;

use App\Models\Election;
use App\Models\Event;
use App\Policies\ElectionPolicy;
use App\Policies\EventPolicy;
use App\Policies\CandidatePolicy;
use App\View\Composers\AlumniLayoutComposer;
use App\View\Composers\PortalModeComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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

        Broadcast::routes(['middleware' => ['web', 'auth']]);

        View::composer([
            'layouts.alumni',
            'layouts.partials.alumni-sidebar',
            'layouts.partials.alumni-top-nav',
            'alumni.partials.feed-right-sidebar',
        ], AlumniLayoutComposer::class);

        View::composer('components.user-avatar-dropdown', PortalModeComposer::class);
        View::composer(['alumni-president.dashboard', 'alumni-president.duties', 'components.layouts.alumni-president'], PortalModeComposer::class);

        Gate::policy(Election::class, ElectionPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(\App\Models\Candidate::class, CandidatePolicy::class);

        // Register Livewire components
        Livewire::component('admin.manage-users', \App\Livewire\Admin\ManageUsers::class);
        Livewire::component('admin.dashboard', \App\Livewire\Admin\Dashboard::class);
        Livewire::component('admin.assign-categories', \App\Livewire\Admin\AssignCategories::class);
        Livewire::component('alumni.clearance-status', \App\Livewire\Alumni\ClearanceStatus::class);
        Livewire::component('alumni.create-event', \App\Livewire\Alumni\CreateEvent::class);
        Livewire::component('alumni.edit-event', \App\Livewire\Alumni\EditEvent::class);
        Livewire::component('alumni.my-events', \App\Livewire\Alumni\MyEvents::class);
        Livewire::component('student-affairs.clearance', \App\Livewire\StudentAffairs\Clearance::class);
        Livewire::component('academic-affairs.clearance', \App\Livewire\AcademicAffairs\Clearance::class);
        Livewire::component('admin.clearance-audit', \App\Livewire\Admin\ClearanceAudit::class);
        Livewire::component('student-affairs.dashboard', \App\Livewire\StudentAffairs\Dashboard::class);
        Livewire::component('student-affairs.audit', \App\Livewire\StudentAffairs\Audit::class);
        Livewire::component('academic-affairs.dashboard', \App\Livewire\AcademicAffairs\Dashboard::class);
        Livewire::component('academic-affairs.audit', \App\Livewire\AcademicAffairs\Audit::class);

        Livewire::component('social.feed', \App\Livewire\Social\Feed::class);
        Livewire::component('social.feed-announcements-strip', \App\Livewire\Social\FeedAnnouncementsStrip::class);
        Livewire::component('social.discover', \App\Livewire\Social\Discover::class);
        Livewire::component('social.feed-official-events-teaser', \App\Livewire\Social\FeedOfficialEventsTeaser::class);
        Livewire::component('social.post-composer', \App\Livewire\Social\PostComposer::class);
        Livewire::component('social.post-card', \App\Livewire\Social\PostCard::class);
        Livewire::component('social.post-comments', \App\Livewire\Social\PostComments::class);
        Livewire::component('social.connection-requests', \App\Livewire\Social\ConnectionRequests::class);
        Livewire::component('social.suggested-connections', \App\Livewire\Social\SuggestedConnections::class);
        Livewire::component('social.event-show', \App\Livewire\Social\EventShow::class);
        Livewire::component('social.notification-bell', \App\Livewire\Social\NotificationBell::class);
    }
}
