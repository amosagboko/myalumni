<x-alumniadmin-dashboard title="Alumni Years | FuLafia Alumni">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Alumni years</h1>
                                <p class="ads-page-subtitle">Define payment year periods used for annual renewals and fee scoping.</p>
                            </div>
                            <a href="{{ route('alumni-years.create') }}" class="btn btn-primary btn-sm ads-btn-primary text-white">
                                <i data-feather="plus" style="width: 15px; height: 15px;"></i>
                                Add year
                            </a>
                        </div>

                        <div class="ads-stats">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Total years</span>
                                <span class="ads-stat-value">{{ number_format($stats['total']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Active</span>
                                <span class="ads-stat-value">{{ number_format($stats['active']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Inactive</span>
                                <span class="ads-stat-value">{{ number_format($stats['inactive']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">With fees</span>
                                <span class="ads-stat-value">{{ number_format($stats['with_fees']) }}</span>
                            </div>
                        </div>

                        @if ($activeYear)
                            <div class="ads-alert">
                                <strong>Active year:</strong> {{ $activeYear->year }}
                                ({{ $activeYear->start_date->format('M j, Y') }} – {{ $activeYear->end_date->format('M j, Y') }})
                                <a href="{{ route('admin.payment-years.show', $activeYear) }}" class="ms-2">Configure dues</a>
                            </div>
                        @else
                            <div class="ads-alert ads-alert-warning">No alumni year is currently active. Activate one before alumni can pay annual renewals.</div>
                        @endif

                        <div class="adt-panel">
                            @if (session('success'))
                                <div class="adt-alert adt-alert-success mx-3 mt-3 mb-0" role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="adt-alert adt-alert-error mx-3 mt-3 mb-0" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if ($alumniYears->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Year</th>
                                                <th>Period</th>
                                                <th>Annual due</th>
                                                <th>Status</th>
                                                <th class="adt-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($alumniYears as $year)
                                                <tr>
                                                    <td class="fw-medium">{{ $year->year }}</td>
                                                    <td class="adt-muted">{{ $year->start_date->format('M j, Y') }} – {{ $year->end_date->format('M j, Y') }}</td>
                                                    <td>
                                                        @if ($year->hasAnnualDueConfigured())
                                                            <span class="fw-medium">₦{{ number_format($year->annualDueTemplate()->amount, 2) }}</span>
                                                        @else
                                                            <span class="adt-tag">Not configured</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="adt-status {{ $year->is_active ? 'adt-status-active' : 'adt-status-inactive' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $year->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="adt-actions">
                                                            <a
                                                                href="{{ route('admin.payment-years.show', $year) }}"
                                                                class="adt-action-btn"
                                                                title="Configure dues"
                                                            >
                                                                <i data-feather="settings" style="width: 14px; height: 14px;"></i>
                                                            </a>
                                                            <a
                                                                href="{{ route('alumni-years.edit', $year) }}"
                                                                class="adt-action-btn"
                                                                title="Edit"
                                                            >
                                                                <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                                            </a>
                                                            @if (! $year->is_active)
                                                                <form
                                                                    action="{{ route('alumni-years.activate', $year) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                >
                                                                    @csrf
                                                                    <button type="submit" class="adt-action-btn" title="Activate">
                                                                        <i data-feather="play-circle" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <form
                                                                    action="{{ route('alumni-years.deactivate', $year) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Deactivate this year? Alumni will not see its annual due until another year is active.')"
                                                                >
                                                                    @csrf
                                                                    <button type="submit" class="adt-action-btn" title="Deactivate">
                                                                        <i data-feather="pause-circle" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            @if (! $year->hasFees())
                                                                <form
                                                                    action="{{ route('alumni-years.destroy', $year) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Delete this alumni year?')"
                                                                >
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="adt-action-btn adt-action-danger" title="Delete">
                                                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($alumniYears->hasPages())
                                    <div class="adt-footer">
                                        <span class="adt-footer-count">
                                            {{ $alumniYears->firstItem() }}–{{ $alumniYears->lastItem() }} of {{ $alumniYears->total() }}
                                        </span>
                                        <div class="adt-pagination">
                                            {{ $alumniYears->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="adt-empty">
                                    <div class="adt-empty-icon">
                                        <i data-feather="calendar" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No alumni years found</h3>
                                    <p class="adt-empty-text">Create a year to define payment periods for annual renewals.</p>
                                    <a href="{{ route('alumni-years.create') }}" class="btn btn-sm ads-btn-primary mt-2">
                                        Add year
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="admin-surface mt-3">
                            <div class="ads-section">
                                <div class="ads-section-card">
                                    <h2 class="ads-section-title">Related</h2>
                                    <p class="small text-muted mb-3">Use Dues Config to set annual renewal amounts and review onboarding fees for each payment year.</p>
                                    <a href="{{ route('admin.payment-years.index') }}" class="btn btn-sm btn-outline-secondary">
                                        Open dues config
                                    </a>
                                </div>
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
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
    @endpush
</x-alumniadmin-dashboard>
