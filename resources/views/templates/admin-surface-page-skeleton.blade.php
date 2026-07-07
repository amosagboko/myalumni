{{--
    Admin surface page skeleton — dashboards, overview pages, non-table layouts.
    Use wrapper class admin-surface for the elevated face-lift.
--}}
<x-admin.surface-styles />

<div class="main-content admin-surface">
    <div class="row">
        <div class="col-12">

            <div class="ads-page-header">
                <div>
                    <h1 class="ads-page-title">{{ $pageTitle ?? 'Page title' }}</h1>
                    <p class="ads-page-subtitle">{{ $pageSubtitle ?? 'Short description.' }}</p>
                </div>
                <div class="ads-filters">
                    <select class="form-select form-select-sm ads-select" name="filter">
                        <option value="">All periods</option>
                    </select>
                </div>
            </div>

            @if (session('message'))
                <div class="ads-alert ads-alert-success" role="alert">{{ session('message') }}</div>
            @endif

            <div class="ads-section">
                <div class="ads-section-card">
                    <h2 class="ads-section-title">Overview</h2>
                    <div class="ads-stats">
                        <div class="ads-stat">
                            <div class="ads-stat-inner">
                                <div>
                                    <span class="ads-stat-label">Total</span>
                                    <span class="ads-stat-value">{{ number_format($stats['total'] ?? 0) }}</span>
                                </div>
                                <span class="ads-stat-icon"><i data-feather="bar-chart-2"></i></span>
                            </div>
                        </div>
                        <div class="ads-stat">
                            <div class="ads-stat-inner">
                                <div>
                                    <span class="ads-stat-label">Active</span>
                                    <span class="ads-stat-value">{{ number_format($stats['active'] ?? 0) }}</span>
                                </div>
                                <span class="ads-stat-icon"><i data-feather="check-circle"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ads-section">
                <div class="ads-section-card">
                    <h2 class="ads-section-title">Quick actions</h2>
                    <div class="ads-quick-actions">
                        <a href="#" class="ads-quick-action">
                            <span class="ads-quick-action-icon"><i data-feather="plus"></i></span>
                            <span>Action one</span>
                        </a>
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
