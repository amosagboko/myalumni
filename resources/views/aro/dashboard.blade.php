<x-layouts.alumni-relations-officer title="Dashboard | Alumni Relations Officer">
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Alumni Relations Officer Dashboard</h1>
                                <p class="ads-page-subtitle">Alumni overview, homepage content, and quick operations.</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('alumni-relations-officer.users') }}" class="btn btn-sm ads-btn-primary text-white">Manage alumni</a>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Overview</h2>
                                <div class="ads-stats">
                                    <div class="ads-stat">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Total alumni</span>
                                                <span class="ads-stat-value">{{ number_format($totalAlumni ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="users"></i></span>
                                        </div>
                                    </div>
                                    <div class="ads-stat">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Active events</span>
                                                <span class="ads-stat-value">{{ number_format($activeEvents ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="calendar"></i></span>
                                        </div>
                                    </div>
                                    <div class="ads-stat">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Pending posts</span>
                                                <span class="ads-stat-value">{{ number_format($pendingPosts ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="file-text"></i></span>
                                        </div>
                                    </div>
                                    <div class="ads-stat ads-stat-highlight">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">New messages</span>
                                                <span class="ads-stat-value">{{ number_format($newMessages ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="mail"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Quick actions</h2>
                                <div class="ads-quick-actions">
                                    <a href="{{ route('alumni-relations-officer.users') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="users"></i></span>
                                        <span>Manage alumni</span>
                                    </a>
                                    <a href="{{ route('upload.alumni') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="upload"></i></span>
                                        <span>Upload alumni</span>
                                    </a>
                                    <a href="{{ route('create.event.index') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="calendar"></i></span>
                                        <span>Homepage content</span>
                                    </a>
                                    <a href="{{ route('retrieve.credentials') }}" class="ads-quick-action">
                                        <span class="ads-quick-action-icon"><i data-feather="key"></i></span>
                                        <span>Retrieve credentials</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h2 class="ads-section-title mb-0 border-0 pb-0">Upcoming events</h2>
                                    <a href="{{ route('create.event.index') }}" class="ads-stat-link">Manage content</a>
                                </div>
                                @if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
                                    <div class="ads-compact-table-wrap">
                                        <table class="ads-compact-table">
                                            <thead>
                                                <tr>
                                                    <th>Event</th>
                                                    <th>Date</th>
                                                    <th>Venue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($upcomingEvents as $event)
                                                    <tr>
                                                        <td>{{ $event->eventname }}</td>
                                                        <td>{{ $event->date?->format('M j, Y') ?? '—' }}</td>
                                                        <td>{{ $event->venue ?: '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                                        <p class="font-xssss text-grey-500 mb-0">
                                            Showing {{ $upcomingEvents->firstItem() }}–{{ $upcomingEvents->lastItem() }}
                                            of {{ $upcomingEvents->total() }}
                                        </p>
                                        {{ $upcomingEvents->links('pagination::bootstrap-5') }}
                                    </div>
                                @else
                                    <p class="ads-empty-inline mb-0">No upcoming events.</p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        if (typeof feather !== 'undefined') feather.replace();
    </script>
    @endpush
</x-layouts.alumni-relations-officer>
