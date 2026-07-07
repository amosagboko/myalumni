<x-alumniadmin-dashboard title="Dues Config | FuLafia Alumni">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Dues config</h1>
                                <p class="ads-page-subtitle">Manage annual renewal dues by payment year. Onboarding category fees are configured separately.</p>
                            </div>
                            <a href="{{ route('admin.payment-years.create') }}" class="btn btn-primary btn-sm ads-btn-primary text-white">
                                <i data-feather="plus" style="width: 15px; height: 15px;"></i>
                                New payment year
                            </a>
                        </div>

                        <div class="ads-stats">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Payment years</span>
                                <span class="ads-stat-value">{{ number_format($stats['total']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Active years</span>
                                <span class="ads-stat-value">{{ number_format($stats['active']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Configured dues</span>
                                <span class="ads-stat-value">{{ number_format($stats['configured']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Paid renewals</span>
                                <span class="ads-stat-value">{{ number_format($stats['paid']) }}</span>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="ads-alert ads-alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="ads-alert ads-alert-error">{{ session('error') }}</div>
                        @endif

                        @if($activeYear)
                            <div class="ads-alert">
                                <strong>Active payment year:</strong> {{ $activeYear->year }}
                                ({{ $activeYear->start_date->format('M j, Y') }} – {{ $activeYear->end_date->format('M j, Y') }})
                                <a href="{{ route('admin.payment-years.show', $activeYear) }}" class="ms-2">Manage</a>
                            </div>
                        @else
                            <div class="ads-alert ads-alert-warning">No payment year is currently active. Activate one before alumni can pay annual dues.</div>
                        @endif

                        <div class="adt-panel">
                            <div class="px-3 pt-3 pb-2 border-bottom">
                                <h2 class="ads-section-title mb-0" style="border: none; padding: 0;">All payment years</h2>
                            </div>

                            @if($years->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Year</th>
                                                <th>Period</th>
                                                <th>Annual due</th>
                                                <th>Paid</th>
                                                <th>Status</th>
                                                <th class="adt-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($years as $year)
                                                <tr>
                                                    <td class="fw-medium">{{ $year->year }}</td>
                                                    <td class="adt-muted">{{ $year->start_date->format('M j, Y') }} – {{ $year->end_date->format('M j, Y') }}</td>
                                                    <td>
                                                        @if($year->annual_due_template)
                                                            <span class="fw-medium">₦{{ number_format($year->annual_due_template->amount, 2) }}</span>
                                                        @else
                                                            <span class="adt-tag">Not configured</span>
                                                        @endif
                                                    </td>
                                                    <td class="adt-muted">{{ number_format($year->annual_paid_count ?? 0) }}</td>
                                                    <td>
                                                        <span class="adt-status {{ $year->is_active ? 'adt-status-active' : 'adt-status-inactive' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $year->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="adt-actions">
                                                            <a href="{{ route('admin.payment-years.show', $year) }}" class="adt-action-btn" title="Manage">
                                                                <i data-feather="settings" style="width: 14px; height: 14px;"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if($years->hasPages())
                                    <div class="adt-footer">
                                        <span class="adt-footer-count">
                                            {{ $years->firstItem() }}–{{ $years->lastItem() }} of {{ $years->total() }}
                                        </span>
                                        <div class="adt-pagination">
                                            {{ $years->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="adt-empty">
                                    <div class="adt-empty-icon">
                                        <i data-feather="calendar" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No payment years yet</h3>
                                    <p class="adt-empty-text">Create a payment year before configuring annual renewal dues.</p>
                                </div>
                            @endif
                        </div>

                        <div class="admin-surface mt-3">
                            <div class="ads-section">
                                <div class="ads-section-card">
                                    <h2 class="ads-section-title">How this works</h2>
                                    <ul class="small text-muted mb-0 ps-3">
                                        <li><strong>Onboarding</strong> — Category fees (registration, levy, etc.) are set under Fee Templates with purpose <em>onboarding</em>. Paid once when an alumnus first registers.</li>
                                        <li><strong>Annual renewal</strong> — One due per payment year for all onboarded alumni. Configure the amount here for each year.</li>
                                        <li><strong>Activate</strong> — Only the active payment year’s annual due is presented to alumni for renewal.</li>
                                    </ul>
                                    <a href="{{ route('admin.fee-templates.index', ['fee_purpose' => 'onboarding']) }}" class="btn btn-sm btn-outline-secondary mt-3">
                                        Manage onboarding fee templates
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
