<x-alumniadmin-dashboard title="Dues Config | FuLafia Alumni">
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="card-body py-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                    <div>
                                        <h5 class="mb-1">Dues Config</h5>
                                        <p class="text-muted small mb-0">
                                            Manage annual renewal dues per payment year. Onboarding category fees are configured separately (one-time at registration).
                                        </p>
                                    </div>
                                    <a href="{{ route('admin.payment-years.create') }}" class="btn btn-primary btn-sm">
                                        <i data-feather="plus" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                        New Payment Year
                                    </a>
                                </div>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($activeYear)
                            <div class="alert alert-info py-2">
                                <strong>Active payment year:</strong> {{ $activeYear->year }}
                                ({{ $activeYear->start_date->format('M j, Y') }} – {{ $activeYear->end_date->format('M j, Y') }})
                                <a href="{{ route('admin.payment-years.show', $activeYear) }}" class="alert-link ms-2">Manage</a>
                            </div>
                        @else
                            <div class="alert alert-warning py-2">No payment year is currently active. Activate one before alumni can pay annual dues.</div>
                        @endif

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">All payment years</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Year</th>
                                                <th>Period</th>
                                                <th>Annual Due</th>
                                                <th>Paid</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($years as $year)
                                                <tr>
                                                    <td class="fw-semibold">{{ $year->year }}</td>
                                                    <td class="small text-muted">
                                                        {{ $year->start_date->format('M j, Y') }} – {{ $year->end_date->format('M j, Y') }}
                                                    </td>
                                                    <td>
                                                        @if($year->annual_due_template)
                                                            <span class="text-success">₦{{ number_format($year->annual_due_template->amount, 2) }}</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark">Not configured</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $year->annual_paid_count ?? 0 }}</td>
                                                    <td>
                                                        @if($year->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="{{ route('admin.payment-years.show', $year) }}" class="btn btn-outline-primary btn-sm">Manage</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-5">No payment years yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if($years->hasPages())
                                <div class="card-footer">{{ $years->links() }}</div>
                            @endif
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <h6 class="card-title">How this works</h6>
                                <ul class="small text-muted mb-0">
                                    <li><strong>Onboarding</strong> — Category fees (registration, levy, etc.) are set under Fee Templates with purpose <em>onboarding</em>. Paid once when an alumnus first registers.</li>
                                    <li><strong>Annual renewal</strong> — One due per payment year for all onboarded alumni. Configure the amount here for each year.</li>
                                    <li><strong>Activate</strong> — Only the active payment year’s annual due is presented to alumni for renewal (once payment logic is enabled).</li>
                                </ul>
                                <a href="{{ route('admin.fee-templates.index', ['fee_purpose' => 'onboarding']) }}" class="btn btn-outline-secondary btn-sm mt-2">
                                    Manage onboarding fee templates
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>
