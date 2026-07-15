<x-layouts.alumni-president title="Dashboard | Alumni President">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">
                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Alumni President Office</h1>
                                <p class="ads-page-subtitle">
                                    Manage association leadership duties, then switch to the member portal for your alumni profile and community features.
                                </p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">President duties</h2>
                                    <p class="ads-page-subtitle mb-3">
                                        Open your president duties dashboard for community events, elections, and leadership activities.
                                    </p>
                                    <a href="{{ route('alumni-president.duties') }}" class="btn btn-sm ads-btn-primary text-white text-decoration-none">
                                        <i data-feather="briefcase" style="width: 14px; height: 14px;"></i>
                                        President duties
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">Member portal</h2>
                                    <p class="ads-page-subtitle mb-3">
                                        As an alumnus, you also use the member portal for newsfeed, discover, payments, and elections participation.
                                    </p>
                                    @if($hasDualPortalAccess ?? false)
                                        <form method="POST" action="{{ route('portal.switch') }}">
                                            @csrf
                                            <input type="hidden" name="mode" value="member">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i data-feather="users" style="width: 14px; height: 14px;"></i>
                                                Open member portal
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">Quick links</h2>
                                    <ul class="list-unstyled mb-0 font-xssss">
                                        <li class="mb-2"><a href="{{ route('alumni.elections') }}">Alumni elections hub</a></li>
                                        <li class="mb-0"><a href="{{ route('alumni.clearance-status') }}">Clearance status</a></li>
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
