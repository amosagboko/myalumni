@extends('layouts.alumni')

@section('content')
<div class="payment-history-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h4 class="fw-600 mb-0">Payment History</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('alumni.payments.index') }}" class="btn btn-outline-primary btn-sm">
                    Current Payments
                </a>
                <a href="{{ route('alumni.home') }}" class="btn btn-outline-secondary btn-sm">
                    Dashboard
                </a>
            </div>
        </div>

        <div class="card-body p-4 w-100 border-0">
            <p class="text-grey-500 font-xssss mb-4">
                A record of all your alumni fee payments, including onboarding, annual dues, and other charges.
            </p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($transactions->isEmpty())
                <div class="text-center py-5">
                    <p class="text-muted mb-3">You have no payment history yet.</p>
                    <a href="{{ route('alumni.payments.index') }}" class="btn btn-primary">
                        View Current Payments
                    </a>
                </div>
            @else
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <div class="text-muted small">Total paid</div>
                            <div class="fs-5 fw-semibold text-success">₦{{ number_format($summary['total_paid'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <div class="text-muted small">Completed payments</div>
                            <div class="fs-5 fw-semibold">{{ number_format($summary['paid_count']) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <div class="text-muted small">Pending</div>
                            <div class="fs-5 fw-semibold {{ $summary['pending_count'] > 0 ? 'text-warning' : '' }}">
                                {{ number_format($summary['pending_count']) }}
                            </div>
                        </div>
                    </div>
                </div>

                @if($summary['pending_count'] > 0)
                    <div class="alert alert-warning mb-4">
                        You have <strong>{{ $summary['pending_count'] }}</strong>
                        pending payment{{ $summary['pending_count'] > 1 ? 's' : '' }}.
                        Use <strong>Pay Now</strong> below, or visit
                        <a href="{{ route('alumni.payments.index') }}" class="alert-link">Current Payments</a>.
                    </div>
                @endif

                {{-- Desktop: table scrolls inside content area only --}}
                <div class="payment-history-table-wrap d-none d-lg-block">
                    <table class="table table-bordered table-sm align-middle mb-0 payment-history-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap">Date</th>
                                <th>Payment</th>
                                <th class="text-nowrap">Year</th>
                                <th class="text-nowrap">Amount</th>
                                <th class="text-nowrap">Status</th>
                                <th class="text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td class="text-nowrap">
                                        <div>{{ $transaction->created_at->format('M j, Y') }}</div>
                                        <small class="text-muted">{{ $transaction->created_at->format('g:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-break">{{ $transaction->display_description }}</div>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            <span class="badge bg-light text-dark border">{{ $transaction->fee_category_label }}</span>
                                        </div>
                                        <small class="text-muted d-block text-truncate payment-ref" title="{{ $transaction->payment_reference }}">
                                            {{ $transaction->payment_reference }}
                                        </small>
                                    </td>
                                    <td class="text-nowrap">
                                        @if($transaction->payment_year_label)
                                            {{ $transaction->payment_year_label }}
                                        @elseif($transaction->feeTemplate?->graduation_year && (int) $transaction->feeTemplate->graduation_year >= 2020)
                                            {{ $transaction->feeTemplate->graduation_year }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap fw-semibold">₦{{ number_format($transaction->amount, 2) }}</td>
                                    <td class="text-nowrap">
                                        @if($transaction->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                            @if($transaction->paid_at)
                                                <div class="small text-muted mt-1">{{ $transaction->paid_at->format('M j, Y') }}</div>
                                            @endif
                                        @elseif($transaction->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if($transaction->status === 'pending')
                                            <a href="{{ route('alumni.payments.process', $transaction) }}" class="btn btn-sm btn-primary">
                                                Pay Now
                                            </a>
                                        @else
                                            <a href="{{ route('alumni.payments.show', $transaction) }}" class="btn btn-sm btn-outline-secondary">
                                                Receipt
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Tablet & mobile: cards avoid horizontal overflow --}}
                <div class="d-lg-none">
                    @foreach($transactions as $transaction)
                        <div class="card mb-3 border shadow-none">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-break">{{ $transaction->display_description }}</div>
                                        <div class="text-muted small">{{ $transaction->created_at->format('M j, Y · g:i A') }}</div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        @if($transaction->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($transaction->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-light text-dark border">{{ $transaction->fee_category_label }}</span>
                                    @if($transaction->payment_year_label)
                                        <span class="badge bg-light text-dark border">{{ $transaction->payment_year_label }}</span>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <div class="fw-semibold">₦{{ number_format($transaction->amount, 2) }}</div>
                                    @if($transaction->status === 'pending')
                                        <a href="{{ route('alumni.payments.process', $transaction) }}" class="btn btn-sm btn-primary flex-shrink-0">
                                            Pay Now
                                        </a>
                                    @else
                                        <a href="{{ route('alumni.payments.show', $transaction) }}" class="btn btn-sm btn-outline-secondary flex-shrink-0">
                                            Receipt
                                        </a>
                                    @endif
                                </div>

                                <div class="small text-muted text-break mt-2">{{ $transaction->payment_reference }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($transactions->hasPages())
                    <div class="payment-history-pagination mt-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<style>
/* Keep content inside the alumni main canvas (beside left nav) */
.payment-history-page {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
    overflow: hidden;
}

.middle-wrap {
    min-width: 0;
    max-width: 100%;
}

.payment-history-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.payment-history-table {
    width: 100%;
    table-layout: fixed;
    margin-bottom: 0;
}

.payment-history-table th,
.payment-history-table td {
    vertical-align: middle;
    word-wrap: break-word;
    overflow-wrap: anywhere;
}

.payment-history-table th:nth-child(1),
.payment-history-table td:nth-child(1) { width: 11%; }
.payment-history-table th:nth-child(2),
.payment-history-table td:nth-child(2) { width: 34%; }
.payment-history-table th:nth-child(3),
.payment-history-table td:nth-child(3) { width: 8%; }
.payment-history-table th:nth-child(4),
.payment-history-table td:nth-child(4) { width: 12%; }
.payment-history-table th:nth-child(5),
.payment-history-table td:nth-child(5) { width: 15%; }
.payment-history-table th:nth-child(6),
.payment-history-table td:nth-child(6) { width: 12%; }

.payment-history-table .payment-ref {
    max-width: 100%;
}

.payment-history-pagination {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
}

.payment-history-pagination .pagination {
    flex-wrap: wrap;
    margin-bottom: 0;
}

.min-w-0 {
    min-width: 0;
}

.flex-shrink-0 {
    flex-shrink: 0;
}
</style>
@endsection
