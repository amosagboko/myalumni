@props([
    'title',
    'subtitle' => 'Clearance overview and recent activity.',
    'clearanceRoute',
    'auditRoute',
    'kpis' => [],
    'recentActivity' => null,
    'faculties' => [],
    'years' => [],
])

<x-admin.surface-styles />

<div class="main-content right-chat-active admin-surface">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left pe-0">
            <div class="row">
                <div class="col-12">

                    <div class="ads-page-header">
                        <div>
                            <h1 class="ads-page-title">{{ $title }}</h1>
                            <p class="ads-page-subtitle">{{ $subtitle }}</p>
                        </div>
                        <div class="ads-page-actions">
                            <a href="{{ $clearanceRoute }}" class="btn btn-sm ads-btn-primary text-white">Go to clearance</a>
                            <a href="{{ $auditRoute }}" class="btn btn-sm btn-outline-secondary">Clearance audit</a>
                        </div>
                    </div>

                    <div class="ads-section">
                        <div class="ads-section-card">
                            <h2 class="ads-section-title">Quick filters</h2>
                            <form action="{{ $clearanceRoute }}" method="get" class="ads-filter-form">
                                <div class="ads-filter-field">
                                    <label for="filter-faculty">Faculty</label>
                                    <select id="filter-faculty" name="faculty" class="form-select form-select-sm ads-select">
                                        <option value="">All faculties</option>
                                        @foreach($faculties as $faculty)
                                            <option value="{{ $faculty }}">{{ $faculty }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field">
                                    <label for="filter-year">Graduation year</label>
                                    <select id="filter-year" name="year" class="form-select form-select-sm ads-select">
                                        <option value="">All years</option>
                                        @foreach($years as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field" style="flex: 0 0 auto; min-width: auto;">
                                    <label class="invisible">Apply</label>
                                    <button type="submit" class="btn btn-sm ads-btn-primary text-white">Apply</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="ads-section">
                        <div class="ads-section-card">
                            <h2 class="ads-section-title">Clearance</h2>
                            <div class="ads-stats">
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Pending</span>
                                            <span class="ads-stat-value">{{ number_format($kpis['pending'] ?? 0) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="clock"></i></span>
                                    </div>
                                </div>
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Cleared today</span>
                                            <span class="ads-stat-value">{{ number_format($kpis['today'] ?? 0) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="check-circle"></i></span>
                                    </div>
                                </div>
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Cleared this week</span>
                                            <span class="ads-stat-value">{{ number_format($kpis['week'] ?? 0) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="ads-stat ads-stat-highlight">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Overall cleared</span>
                                            <span class="ads-stat-value">{{ number_format($kpis['overall'] ?? 0) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="users"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ads-section">
                        <div class="ads-section-card">
                            <h2 class="ads-section-title">Recent activity</h2>
                            @if($recentActivity && $recentActivity->count() > 0)
                                <div class="ads-compact-table-wrap">
                                    <table class="ads-compact-table">
                                        <thead>
                                            <tr>
                                                <th>When</th>
                                                <th>Alumni</th>
                                                <th>Matric</th>
                                                <th>Change</th>
                                                <th>Actor</th>
                                                <th>Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentActivity as $log)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('M j, H:i') }}</td>
                                                    <td>{{ $log->alumni_name }}</td>
                                                    <td>{{ $log->matric_number }}</td>
                                                    <td>{{ $log->old_value ? '✔' : '✖' }} → {{ $log->new_value ? '✔' : '✖' }}</td>
                                                    <td>{{ $log->actor_name }}</td>
                                                    <td>{{ $log->reason ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="ads-empty-inline mb-0">No recent activity.</p>
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
    document.addEventListener('livewire:navigated', () => { if (typeof feather !== 'undefined') feather.replace(); });
</script>
@endpush
