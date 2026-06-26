<x-alumniadmin-dashboard title="Payment Year {{ $paymentYear->year }} | FuLafia Alumni">
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <a href="{{ route('admin.payment-years.index') }}" class="text-muted small text-decoration-none">&larr; Dues config</a>
                                <h5 class="mb-0 mt-1">Payment year {{ $paymentYear->year }}</h5>
                                <p class="text-muted small mb-0">
                                    {{ $paymentYear->start_date->format('M j, Y') }} – {{ $paymentYear->end_date->format('M j, Y') }}
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                @if(!$paymentYear->is_active)
                                    <form action="{{ route('admin.payment-years.activate', $paymentYear) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Activate this year</button>
                                    </form>
                                @else
                                    <span class="badge bg-success align-self-center py-2 px-3">Active payment year</span>
                                @endif
                            </div>
                        </div>

                        @foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $class)
                            @if(session($key))
                                <div class="alert alert-{{ $class }} alert-dismissible fade show">{{ session($key) }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                        @endforeach

                        {{-- Annual renewal due --}}
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Annual renewal due</h6>
                                    <small class="text-muted">Single yearly fee for onboarded alumni (not category bundle)</small>
                                </div>
                                @if(!$annualDueTemplate && $previousAnnualDue)
                                    <form action="{{ route('admin.payment-years.copy-annual-due', $paymentYear) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            Copy from {{ $previousYear->year }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="card-body">
                                @if($annualDueTemplate)
                                    <form action="{{ route('admin.payment-years.annual-due.update', [$paymentYear, $annualDueTemplate]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Amount (₦)</label>
                                                <input type="number" name="amount" step="0.01" min="0" class="form-control"
                                                    value="{{ old('amount', $annualDueTemplate->amount) }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Valid from</label>
                                                <input type="date" name="valid_from" class="form-control"
                                                    value="{{ old('valid_from', $annualDueTemplate->valid_from?->format('Y-m-d')) }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Valid until</label>
                                                <input type="date" name="valid_until" class="form-control"
                                                    value="{{ old('valid_until', $annualDueTemplate->valid_until?->format('Y-m-d')) }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Description</label>
                                                <input type="text" name="description" class="form-control"
                                                    value="{{ old('description', $annualDueTemplate->description) }}">
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="annual_active"
                                                        {{ old('is_active', $annualDueTemplate->is_active) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="annual_active">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div class="small text-muted">
                                                <span class="me-3"><strong>{{ $annualStats['paid'] }}</strong> paid</span>
                                                <span><strong>{{ $annualStats['pending'] }}</strong> pending</span>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">Save annual due</button>
                                        </div>
                                    </form>
                                @else
                                    <p class="text-muted small">No annual due configured for {{ $paymentYear->year }} yet. Alumni cannot pay this year’s renewal until you set it.</p>
                                    <form action="{{ route('admin.payment-years.annual-due.store', $paymentYear) }}" method="POST">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Amount (₦)</label>
                                                <input type="number" name="amount" step="0.01" min="0" class="form-control"
                                                    value="{{ old('amount', $previousAnnualDue?->amount ?? '') }}" required
                                                    placeholder="{{ $previousAnnualDue ? number_format($previousAnnualDue->amount, 2) : 'e.g. 2000' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Valid from</label>
                                                <input type="date" name="valid_from" class="form-control"
                                                    value="{{ old('valid_from', $paymentYear->start_date->format('Y-m-d')) }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Valid until</label>
                                                <input type="date" name="valid_until" class="form-control"
                                                    value="{{ old('valid_until', $paymentYear->end_date->format('Y-m-d')) }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Description</label>
                                                <input type="text" name="description" class="form-control"
                                                    value="{{ old('description', "Annual alumni due for {$paymentYear->year}") }}">
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="new_annual_active" checked>
                                                    <label class="form-check-label" for="new_annual_active">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm mt-3">Create annual due</button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Onboarding fees reference --}}
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Onboarding fees (category-based)</h6>
                                    <small class="text-muted">One-time at registration — not tied to payment year</small>
                                </div>
                                <a href="{{ route('admin.fee-templates.create') }}" class="btn btn-outline-primary btn-sm">Add template</a>
                            </div>
                            <div class="card-body p-0">
                                @if($onboardingByCohort->isEmpty())
                                    <p class="text-muted small p-3 mb-0">No onboarding templates configured. Add registration, development levy, data processing, and tech support fees per graduation cohort and category.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead class="table-light">
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
                                                                <a href="{{ route('admin.fee-templates.edit', $template) }}" class="btn btn-link btn-sm p-0">Edit</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-white">
                                <a href="{{ route('admin.fee-templates.index', ['fee_purpose' => 'onboarding']) }}" class="small">View all onboarding templates &rarr;</a>
                            </div>
                        </div>

                        <div class="card border-info">
                            <div class="card-body small text-muted">
                                When this payment year is active and an annual due is configured, eligible alumni receive a pending payable automatically on activation and via the daily <code>dues:assign-annual</code> job. Alumni also get one assigned when they open their payments page.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>
