<x-layouts.elcom-chairman title="Dashboard | ELCOM Chairman">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">ELCOM Chairman Dashboard</h1>
                                <p class="ads-page-subtitle">Election overview and participation metrics.</p>
                            </div>
                            <div class="ads-page-actions d-flex flex-wrap gap-2">
                                <a href="{{ route('elcom-chairman.elections.index') }}" class="btn btn-sm ads-btn-primary text-white">
                                    <i data-feather="award" style="width: 14px; height: 14px;"></i>
                                    All elections
                                </a>
                                <a href="{{ route('elcom-chairman.elections.create') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="plus" style="width: 14px; height: 14px;"></i>
                                    Create election
                                </a>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <div>
                                        <h2 class="ads-section-title mb-0 border-0 pb-0">Current cycle</h2>
                                        <p class="ads-page-subtitle mb-0 mt-1">
                                            @if ($selectedYear)
                                                Showing {{ $selectedYear }}
                                                @if (!empty($cycleLabel))
                                                    — {{ $cycleLabel }}
                                                @endif
                                            @else
                                                No election years found
                                            @endif
                                        </p>
                                    </div>
                                    @if ($availableYears->isNotEmpty())
                                        <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
                                            <label for="cycle-year" class="small text-muted mb-0">Election year</label>
                                            <select
                                                id="cycle-year"
                                                name="year"
                                                class="form-select form-select-sm ads-select"
                                                style="width: auto; min-width: 110px;"
                                                onchange="this.form.submit()"
                                            >
                                                @foreach ($availableYears as $year)
                                                    <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>{{ $year }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                </div>
                                <div class="ads-stats mb-3">
                                    <div class="ads-stat">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Active elections</span>
                                                <span class="ads-stat-value">{{ number_format($activeElections ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="activity"></i></span>
                                        </div>
                                    </div>
                                    <div class="ads-stat">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Total EOI paid</span>
                                                <span class="ads-stat-value">{{ number_format($totalCandidates ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="file-text"></i></span>
                                        </div>
                                    </div>
                                    <div class="ads-stat">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Votes cast</span>
                                                <span class="ads-stat-value">{{ number_format($totalVotes ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="check-square"></i></span>
                                        </div>
                                    </div>
                                    <div class="ads-stat ads-stat-highlight">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Dues-paid alumni</span>
                                                <span class="ads-stat-value">{{ number_format($paidDuesAlumni ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="award"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ads-stats">
                                    <div class="ads-stat">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Elections in year</span>
                                                <span class="ads-stat-value">{{ number_format($totalElections ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="layers"></i></span>
                                        </div>
                                    </div>
                                    <div class="ads-stat">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Completed</span>
                                                <span class="ads-stat-value">{{ number_format($completedElections ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="flag"></i></span>
                                        </div>
                                    </div>
                                    <div class="ads-stat">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Draft / pending</span>
                                                <span class="ads-stat-value">{{ number_format($pendingElections ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="edit-3"></i></span>
                                        </div>
                                    </div>
                                    <div class="ads-stat">
                                        <div class="ads-stat-inner">
                                            <div>
                                                <span class="ads-stat-label">Accredited voters</span>
                                                <span class="ads-stat-value">{{ number_format($totalAccreditedVoters ?? 0) }}</span>
                                            </div>
                                            <span class="ads-stat-icon"><i data-feather="user-check"></i></span>
                                        </div>
                                    </div>
                                </div>
                                @if (($archivedElections ?? 0) > 0)
                                    <p class="small text-muted mb-0 mt-3">
                                        {{ number_format($archivedElections) }} archived election(s) in {{ $selectedYear }} are included when you select that year.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h2 class="ads-section-title mb-0 border-0 pb-0">
                                        Recent elections
                                        @if ($selectedYear)
                                            <span class="fw-normal text-muted small">({{ $selectedYear }})</span>
                                        @endif
                                    </h2>
                                    <a href="{{ route('elcom-chairman.elections.index') }}" class="ads-stat-link">View all</a>
                                </div>
                                @if(isset($recentElections) && $recentElections->count() > 0)
                                    <div class="ads-compact-table-wrap">
                                        <table class="ads-compact-table">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Status</th>
                                                    <th>Start</th>
                                                    <th>End</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentElections as $election)
                                                    <tr>
                                                        <td class="fw-medium">{{ $election->title }}</td>
                                                        <td>
                                                            <span class="adt-status {{ $election->status === 'completed' ? 'adt-status-active' : ($election->status === 'draft' ? 'adt-status-suspended' : 'adt-status-active') }}">
                                                                <span class="adt-status-dot"></span>
                                                                {{ ucfirst($election->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="adt-muted">{{ $election->start_date?->format('M j, Y') ?? '—' }}</td>
                                                        <td class="adt-muted">{{ $election->end_date?->format('M j, Y') ?? '—' }}</td>
                                                        <td class="text-end">
                                                            <a href="{{ route('elcom-chairman.elections.show', $election) }}" class="btn btn-sm btn-outline-primary py-0">View</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="ads-empty-inline mb-0">No elections yet.</p>
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
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
        document.addEventListener('livewire:navigated', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>
    @endpush
</x-layouts.elcom-chairman>
