<x-alumniadmin-dashboard title="Payment Year {{ $paymentYear->year }} | FuLafia Alumni">
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Payment year {{ $paymentYear->year }}</h1>
                                <p class="ads-page-subtitle">
                                    {{ $paymentYear->start_date->format('M j, Y') }} – {{ $paymentYear->end_date->format('M j, Y') }}
                                </p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('admin.payment-years.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Dues config
                                </a>
                                @if(!$paymentYear->is_active)
                                    <form action="{{ route('admin.payment-years.activate', $paymentYear) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm ads-btn-primary">Activate this year</button>
                                    </form>
                                @else
                                    <span class="adt-status adt-status-active">
                                        <span class="adt-status-dot"></span>
                                        Active payment year
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="ads-stats ads-stats-3">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Renewal paid</span>
                                <span class="ads-stat-value">{{ number_format($annualStats['paid']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Renewal pending</span>
                                <span class="ads-stat-value">{{ number_format($annualStats['pending']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Onboarding cohorts</span>
                                <span class="ads-stat-value">{{ number_format($onboardingByCohort->count()) }}</span>
                            </div>
                        </div>

                        @foreach (['success' => 'success', 'error' => 'error', 'info' => null] as $key => $class)
                            @if(session($key))
                                <div class="ads-alert{{ $class ? ' ads-alert-' . $class : '' }}">{{ session($key) }}</div>
                            @endif
                        @endforeach

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div>
                                        <h2 class="ads-section-title mb-1">Annual renewal due</h2>
                                        <p class="small text-muted mb-0">Single yearly fee for onboarded alumni, separate from category onboarding fees.</p>
                                    </div>
                                    @if(!$yearSpecificAnnualDue && $previousAnnualDue)
                                        <form action="{{ route('admin.payment-years.copy-annual-due', $paymentYear) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                Copy from {{ $previousYear->year }}
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                @if($yearSpecificAnnualDue)
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="adt-tag">Configured for {{ $paymentYear->year }}</span>
                                        <span class="small text-muted">Template #{{ $yearSpecificAnnualDue->id }}</span>
                                    </div>
                                    <form action="{{ route('admin.payment-years.annual-due.update', [$paymentYear, $yearSpecificAnnualDue]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Amount (₦)</label>
                                                <input type="number" name="amount" step="0.01" min="0" class="form-control form-control-sm"
                                                    value="{{ old('amount', $yearSpecificAnnualDue->amount) }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Valid from</label>
                                                <input type="date" name="valid_from" class="form-control form-control-sm"
                                                    value="{{ old('valid_from', $yearSpecificAnnualDue->valid_from?->format('Y-m-d')) }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Valid until</label>
                                                <input type="date" name="valid_until" class="form-control form-control-sm"
                                                    value="{{ old('valid_until', $yearSpecificAnnualDue->valid_until?->format('Y-m-d')) }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Description</label>
                                                <input type="text" name="description" class="form-control form-control-sm"
                                                    value="{{ old('description', $yearSpecificAnnualDue->description) }}">
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="annual_active"
                                                        {{ old('is_active', $yearSpecificAnnualDue->is_active) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="annual_active">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div class="small text-muted">
                                                <span class="me-3"><strong>{{ $annualStats['paid'] }}</strong> paid</span>
                                                <span><strong>{{ $annualStats['pending'] }}</strong> pending</span>
                                            </div>
                                            <button type="submit" class="btn btn-sm ads-btn-primary">Update annual due</button>
                                        </div>
                                    </form>
                                @elseif($sharedAnnualDue)
                                    <div class="ads-alert mb-3">
                                        <strong>Using shared annual due.</strong>
                                        This payment year has no dedicated template yet. Alumni currently pay
                                        <strong>₦{{ number_format($sharedAnnualDue->amount, 2) }}</strong>
                                        from
                                        @if((int) $sharedAnnualDue->graduation_year === \App\Models\FeeTemplate::PAYMENT_YEAR_ALL)
                                            the <em>All payment years</em> template
                                        @else
                                            a legacy subscription template
                                        @endif
                                        ({{ $sharedAnnualDue->description ?? 'Annual due' }}).
                                        <a href="{{ route('admin.fee-templates.edit', $sharedAnnualDue) }}" class="alert-link">Edit shared template</a>
                                        or create a year-specific amount below.
                                    </div>
                                    <p class="text-muted small mb-2">Create a dedicated annual due for {{ $paymentYear->year }}:</p>
                                    @include('admin.payment-years.partials.annual-due-create-form', [
                                        'paymentYear' => $paymentYear,
                                        'previousAnnualDue' => $sharedAnnualDue,
                                    ])
                                @else
                                    <p class="text-muted small">No annual due configured for {{ $paymentYear->year }} yet. Alumni cannot pay this year’s renewal until you set it.</p>
                                    @include('admin.payment-years.partials.annual-due-create-form', [
                                        'paymentYear' => $paymentYear,
                                        'previousAnnualDue' => $previousAnnualDue,
                                    ])
                                @endif
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div>
                                        <h2 class="ads-section-title mb-1">Onboarding fees</h2>
                                        <p class="small text-muted mb-0">One-time category-based fees at registration, not tied to payment year.</p>
                                    </div>
                                    <a href="{{ route('admin.fee-templates.create') }}" class="btn btn-sm btn-outline-secondary">Add template</a>
                                </div>

                                @if($onboardingByCohort->isEmpty())
                                    <p class="text-muted small mb-0">No onboarding templates configured. Add registration, development levy, data processing, and tech support fees per graduation cohort and category.</p>
                                @else
                                    <div class="ads-compact-table-wrap">
                                        <table class="ads-compact-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Cohort year</th>
                                                    <th>Category</th>
                                                    <th>Fee type</th>
                                                    <th>Amount</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($onboardingByCohort as $cohortYear => $templates)
                                                    @foreach($templates as $template)
                                                        <tr>
                                                            @if($loop->first)
                                                                <td rowspan="{{ $templates->count() }}" class="align-middle fw-semibold">{{ $cohortYear }}</td>
                                                            @endif
                                                            <td>{{ $template->category?->name ?? '—' }}</td>
                                                            <td>{{ $template->feeType?->name }}</td>
                                                            <td>₦{{ number_format($template->amount, 2) }}</td>
                                                            <td class="text-end">
                                                                <a href="{{ route('admin.fee-templates.edit', $template) }}" class="small">Edit</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                                <a href="{{ route('admin.fee-templates.index', ['fee_purpose' => 'onboarding']) }}" class="small d-inline-block mt-3">View all onboarding templates →</a>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Notes</h2>
                                <div class="small text-muted">
                                When this payment year is active and an annual due is configured, eligible alumni receive a pending payable automatically on activation and via the daily <code>dues:assign-annual</code> job. Alumni also get one assigned when they open their payments page.
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
