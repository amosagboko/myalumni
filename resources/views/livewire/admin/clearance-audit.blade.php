<div>
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Clearance audit</h1>
                                <p class="ads-page-subtitle">Track clearance status changes across student and academic affairs.</p>
                            </div>
                            <button type="button" class="btn btn-sm ads-btn-primary" wire:click="export">
                                <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                Export
                            </button>
                        </div>

                        <div class="ads-stats">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Total logs</span>
                                <span class="ads-stat-value">{{ number_format($stats['total']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Student affairs</span>
                                <span class="ads-stat-value">{{ number_format($stats['student_affairs']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Academic affairs</span>
                                <span class="ads-stat-value">{{ number_format($stats['academic_affairs']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Matching filters</span>
                                <span class="ads-stat-value">{{ number_format($stats['filtered']) }}</span>
                            </div>
                        </div>

                        <div class="adt-panel">
                            @if (session()->has('success'))
                                <div class="adt-alert adt-alert-success mx-3 mt-3 mb-0" role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session()->has('error'))
                                <div class="adt-alert adt-alert-error mx-3 mt-3 mb-0" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <div class="adt-toolbar">
                                <div class="adt-filters">
                                    <select wire:model.live="division" class="form-select form-select-sm adt-select">
                                        <option value="">All divisions</option>
                                        <option value="student_affairs">Student affairs</option>
                                        <option value="academic_affairs">Academic affairs</option>
                                    </select>
                                    <div class="adt-search">
                                        <i data-feather="search" class="adt-search-icon"></i>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.500ms="alumniName"
                                            class="form-control form-control-sm"
                                            placeholder="Alumni name"
                                        >
                                    </div>
                                    <div class="adt-search">
                                        <i data-feather="user" class="adt-search-icon"></i>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.500ms="actorName"
                                            class="form-control form-control-sm"
                                            placeholder="Actor name"
                                        >
                                    </div>
                                    <input
                                        type="date"
                                        wire:model.live="dateFrom"
                                        class="form-control form-control-sm adt-select adt-select-narrow"
                                        aria-label="Date from"
                                    >
                                    <input
                                        type="date"
                                        wire:model.live="dateTo"
                                        class="form-control form-control-sm adt-select adt-select-narrow"
                                        aria-label="Date to"
                                    >
                                </div>
                            </div>

                            @if ($logs->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>When</th>
                                                <th>Alumni</th>
                                                <th>Matric</th>
                                                <th>Division</th>
                                                <th>Old → New</th>
                                                <th>Actor</th>
                                                <th>Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($logs as $log)
                                                <tr wire:key="clearance-log-{{ $log->id }}">
                                                    <td class="adt-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('M j, Y H:i') }}</td>
                                                    <td class="fw-medium">{{ $log->alumni_name }}</td>
                                                    <td>{{ $log->matric_number }}</td>
                                                    <td>
                                                        <span class="adt-tag">
                                                            {{ $log->division === 'student_affairs' ? 'Student affairs' : 'Academic affairs' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $log->old_value ? 'Cleared' : 'Pending' }} → {{ $log->new_value ? 'Cleared' : 'Pending' }}</td>
                                                    <td>
                                                        <div class="fw-medium">{{ $log->actor_name }}</div>
                                                        <div class="adt-muted small">{{ $log->actor_role }}</div>
                                                    </td>
                                                    <td class="adt-muted">{{ $log->reason ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($logs->hasPages())
                                    <div class="adt-footer">
                                        <span class="adt-footer-count">
                                            {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}
                                        </span>
                                        <div class="adt-pagination">
                                            {{ $logs->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="adt-empty">
                                    <div class="adt-empty-icon">
                                        <i data-feather="clipboard" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No audit logs found</h3>
                                    <p class="adt-empty-text">Try adjusting your filters to see clearance history.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
        document.addEventListener('livewire:navigated', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
    @endpush
</div>
