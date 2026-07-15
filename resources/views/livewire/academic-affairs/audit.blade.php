<div>
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content admin-surface admin-data-table" style="padding-right: 1.25rem;">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Academic Affairs Clearance Audit</h1>
                                <p class="ads-page-subtitle">Track academic affairs clearance status changes.</p>
                            </div>
                            <div class="ads-page-actions d-flex flex-wrap gap-2">
                                <a href="{{ route('academic-affairs.home') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Dashboard
                                </a>
                                <a href="{{ route('academic-affairs.clearance') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="user-check" style="width: 14px; height: 14px;"></i>
                                    Clearance
                                </a>
                                <button type="button" class="btn btn-sm ads-btn-primary text-white" wire:click="export">
                                    <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                    Export CSV
                                </button>
                            </div>
                        </div>

                        <div class="adt-panel">
                            <div class="adt-toolbar">
                                <div class="adt-filters">
                                    <div class="adt-search">
                                        <i data-feather="search" class="adt-search-icon"></i>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.400ms="alumniName"
                                            class="form-control form-control-sm"
                                            placeholder="Alumni name…"
                                        >
                                    </div>
                                    <div class="adt-search">
                                        <i data-feather="user" class="adt-search-icon"></i>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.400ms="actorName"
                                            class="form-control form-control-sm"
                                            placeholder="Actor name…"
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
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="clearFilters">
                                        Clear
                                    </button>
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
                                                <th>Status change</th>
                                                <th>Actor</th>
                                                <th>Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($logs as $log)
                                                <tr wire:key="aa-audit-log-{{ $log->id }}">
                                                    <td class="adt-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('M j, Y H:i') }}</td>
                                                    <td class="fw-medium">{{ $log->alumni_name }}</td>
                                                    <td>{{ $log->matric_number }}</td>
                                                    <td>
                                                        <span class="adt-status {{ $log->old_value ? 'adt-status-active' : 'adt-status-suspended' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $log->old_value ? 'Cleared' : 'Pending' }}
                                                        </span>
                                                        <span class="adt-muted mx-1">→</span>
                                                        <span class="adt-status {{ $log->new_value ? 'adt-status-active' : 'adt-status-suspended' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $log->new_value ? 'Cleared' : 'Pending' }}
                                                        </span>
                                                    </td>
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
            if (typeof feather !== 'undefined') feather.replace();
        });
        document.addEventListener('livewire:navigated', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
        document.addEventListener('livewire:init', function () {
            Livewire.hook('morph.updated', () => {
                if (typeof feather !== 'undefined') feather.replace();
            });
        });
    </script>
    @endpush
</div>
