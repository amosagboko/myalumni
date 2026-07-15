<x-layouts.alumni-president title="President Duties | Alumni President">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">
                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">President Duties</h1>
                                <p class="ads-page-subtitle">
                                    Manage association leadership activities: community events and member engagement.
                                </p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('alumni-president.home') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Back to office
                                </a>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="ads-section-card h-100 text-center">
                                    <div class="font-xssss text-grey-500 text-uppercase fw-600">Community events</div>
                                    <div class="fw-700 font-xl text-grey-900 mt-2">{{ $eventStats['total'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="ads-section-card h-100 text-center">
                                    <div class="font-xssss text-grey-500 text-uppercase fw-600">Published events</div>
                                    <div class="fw-700 font-xl text-success mt-2">{{ $eventStats['published'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="ads-section-card h-100 text-center">
                                    <div class="font-xssss text-grey-500 text-uppercase fw-600">Pending review</div>
                                    <div class="fw-700 font-xl text-warning mt-2">{{ $eventStats['pending'] }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            @can('create event')
                            <div class="col-lg-6">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">Community events</h2>
                                    <p class="ads-page-subtitle mb-3">
                                        Create and manage alumni community events submitted for Discover.
                                    </p>
                                    <div class="d-flex flex-wrap gap-2 mb-4">
                                        <a href="{{ route('alumni.events.create') }}" class="btn btn-sm ads-btn-primary text-white text-decoration-none">
                                            <i data-feather="plus" style="width: 14px; height: 14px;"></i>
                                            Create event
                                        </a>
                                        <a href="{{ route('alumni.events.mine') }}" class="btn btn-sm btn-outline-secondary text-decoration-none">
                                            <i data-feather="calendar" style="width: 14px; height: 14px;"></i>
                                            My events
                                        </a>
                                    </div>

                                    @if($communityEvents->isEmpty())
                                        <p class="font-xssss text-grey-500 mb-0">No community events created yet.</p>
                                    @else
                                        <ul class="list-unstyled mb-0 font-xssss">
                                            @foreach($communityEvents as $event)
                                                <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                    <span>{{ $event->eventname }}</span>
                                                    <span class="badge {{ $event->is_published ? 'bg-success' : 'bg-warning text-dark' }}">
                                                        {{ $event->is_published ? 'Published' : 'Pending' }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                            @endcan

                            <div class="col-lg-6">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">Member engagement</h2>
                                    <p class="ads-page-subtitle mb-3">
                                        Quick access to member-facing tools while carrying out presidential duties.
                                    </p>
                                    <ul class="list-unstyled mb-0 font-xssss">
                                        <li class="mb-2">
                                            <a href="{{ route('alumni.discover') }}">Browse Discover (events &amp; news)</a>
                                        </li>
                                        <li class="mb-2">
                                            <a href="{{ route('alumni.home') }}">View alumni newsfeed</a>
                                        </li>
                                        <li class="mb-0">
                                            <a href="{{ route('alumni.clearance-status') }}">Check clearance status</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.alumni-president>
