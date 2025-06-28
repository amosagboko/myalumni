@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">EOI Payment Status - {{ $election->title }}</h4>
                    <a href="{{ route('elcom.elections.show', $election) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Election
                    </a>
                </div>
                <div class="card-body">
                    <!-- EOI Timeline Status -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">EOI Timeline</h6>
                                    <p class="mb-1">
                                        <strong>Start:</strong> {{ $election->eoi_start?->format('M d, Y h:i A') ?? 'Not set' }}
                                    </p>
                                    <p class="mb-1">
                                        <strong>End:</strong> {{ $election->eoi_end?->format('M d, Y h:i A') ?? 'Not set' }}
                                    </p>
                                    <p class="mb-0">
                                        <strong>Status:</strong> 
                                        @if($election->isEoiPeriodActive())
                                            <span class="badge bg-success">Active</span>
                                        @elseif($election->hasEoiEnded())
                                            <span class="badge bg-secondary">Ended</span>
                                        @else
                                            <span class="badge bg-warning">Not Started</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Payment Statistics</h6>
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <h4 class="text-primary">{{ $totalApplications }}</h4>
                                            <small class="text-muted">Total Applications</small>
                                        </div>
                                        <div class="col-4">
                                            <h4 class="text-success">{{ $paidApplications }}</h4>
                                            <small class="text-muted">Paid</small>
                                        </div>
                                        <div class="col-4">
                                            <h4 class="text-warning">{{ $pendingPayments }}</h4>
                                            <small class="text-muted">Pending Payment</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Extension Options -->
                    @if($election->canExtendEoiPeriod() && $pendingPayments > 0)
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    EOI Period Extension Available
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">
                                    <strong>{{ $pendingPayments }}</strong> applications have pending payments. 
                                    You can extend the EOI period to allow more time for payments to be completed.
                                </p>
                                
                                <form action="{{ route('elcom.elections.extend-eoi', $election) }}" method="POST" class="row g-3">
                                    @csrf
                                    <div class="col-md-4">
                                        <label for="extension_days" class="form-label">Extension Period (Days)</label>
                                        <select name="extension_days" id="extension_days" class="form-select" required>
                                            <option value="">Select days</option>
                                            <option value="3">3 days</option>
                                            <option value="7" selected>7 days</option>
                                            <option value="14">14 days</option>
                                            <option value="21">21 days</option>
                                            <option value="30">30 days</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">New End Date</label>
                                        <div class="form-control-plaintext">
                                            <span id="new-end-date">
                                                {{ $election->eoi_end?->addDays(7)->format('M d, Y h:i A') ?? 'Not available' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-clock me-2"></i>
                                            Extend EOI Period
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Pending Payments List -->
                    @if($pendingPayments > 0)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Pending Payments ({{ $pendingPayments }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Candidate</th>
                                                <th>Office</th>
                                                <th>Application Date</th>
                                                <th>Payment Reference</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($election->candidates()->where('has_paid_screening_fee', false)->with(['alumni.user', 'office'])->get() as $candidate)
                                                <tr>
                                                    <td>{{ $candidate->alumni->user->name }}</td>
                                                    <td>{{ $candidate->office->title }}</td>
                                                    <td>{{ $candidate->created_at->format('M d, Y h:i A') }}</td>
                                                    <td>
                                                        @php
                                                            $transaction = \App\Models\Transaction::where('alumni_id', $candidate->alumni_id)
                                                                ->whereHas('feeTemplate', function($q) use ($candidate) {
                                                                    $q->where('fee_type_id', $candidate->office->fee_type_id);
                                                                })
                                                                ->where('status', 'pending')
                                                                ->first();
                                                        @endphp
                                                        {{ $transaction?->payment_reference ?? 'N/A' }}
                                                    </td>
                                                    <td>₦{{ number_format($transaction?->amount ?? 0, 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-warning">Pending Payment</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Recommendations -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Recommendations</h5>
                        </div>
                        <div class="card-body">
                            @if($pendingPayments > 0 && $election->hasEoiEnded())
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle me-2"></i>Consider Extending EOI Period</h6>
                                    <p class="mb-0">
                                        There are {{ $pendingPayments }} pending payments. Consider extending the EOI period 
                                        to allow candidates more time to complete their payments.
                                    </p>
                                </div>
                            @elseif($pendingPayments == 0 && $election->hasEoiEnded())
                                <div class="alert alert-success">
                                    <h6><i class="bi bi-check-circle me-2"></i>All Payments Complete</h6>
                                    <p class="mb-0">
                                        All EOI applications have been paid for. You can proceed with the screening process.
                                    </p>
                                </div>
                            @elseif($election->isEoiPeriodActive())
                                <div class="alert alert-warning">
                                    <h6><i class="bi bi-clock me-2"></i>EOI Period Active</h6>
                                    <p class="mb-0">
                                        The EOI period is still active. Monitor payment completion and consider extending 
                                        if needed before the period ends.
                                    </p>
                                </div>
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
document.addEventListener('DOMContentLoaded', function() {
    const extensionSelect = document.getElementById('extension_days');
    const newEndDateSpan = document.getElementById('new-end-date');
    
    if (extensionSelect && newEndDateSpan) {
        extensionSelect.addEventListener('change', function() {
            const days = parseInt(this.value) || 7;
            const currentEndDate = new Date('{{ $election->eoi_end?->format("Y-m-d H:i:s") }}');
            const newEndDate = new Date(currentEndDate.getTime() + (days * 24 * 60 * 60 * 1000));
            
            newEndDateSpan.textContent = newEndDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        });
    }
});
</script>
@endpush
@endsection 