@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-5" style="margin-left: 150px; margin-top: 100px !important;">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment History</h5>
                </div>
                <div class="card-body small">
                    @if($transactions->isEmpty())
                        <div class="alert alert-info small">
                            You have no payment history yet.
                        </div>
                    @else
                        @php
                            $pendingCount = $transactions->where('status', 'pending')->count();
                        @endphp
                        
                        @if($pendingCount > 0)
                            <div class="alert alert-warning small mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                You have <strong>{{ $pendingCount }}</strong> pending payment{{ $pendingCount > 1 ? 's' : '' }}. 
                                Click the "Pay" button next to any pending transaction to complete your payment.
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">Date</th>
                                        <th class="small">Fee Type</th>
                                        <th class="small">Year</th>
                                        <th class="small">Amount</th>
                                        <th class="small">Reference</th>
                                        <th class="small">Status</th>
                                        <th class="small">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td class="small">
                                                {{ $transaction->created_at->format('M d, Y H:i A') }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ $transaction->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td class="small">{{ $transaction->feeTemplate->feeType->name }}</td>
                                            <td class="small">{{ $transaction->feeTemplate->graduation_year }}</td>
                                            <td class="small">₦{{ number_format($transaction->amount, 2) }}</td>
                                            <td class="small">{{ $transaction->payment_reference }}</td>
                                            <td class="small">
                                                @if($transaction->status === 'paid')
                                                    <span class="badge bg-success small">Paid</span>
                                                @elseif($transaction->status === 'pending')
                                                    <span class="badge bg-warning small">Pending</span>
                                                @else
                                                    <span class="badge bg-danger small">Failed</span>
                                                @endif
                                            </td>
                                            <td class="small">
                                                @if($transaction->status === 'pending')
                                                    <a href="{{ route('alumni.payments.process', $transaction) }}" 
                                                       class="btn btn-success btn-sm" 
                                                       title="Complete Payment">
                                                        <i class="fas fa-credit-card me-1"></i> Pay
                                                    </a>
                                                @elseif($transaction->status === 'paid')
                                                    <a href="{{ route('alumni.payments.show', $transaction) }}" 
                                                       class="btn btn-info btn-sm" 
                                                       title="View Details">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </a>
                                                @else
                                                    <a href="{{ route('alumni.payments.show', $transaction) }}" 
                                                       class="btn btn-secondary btn-sm" 
                                                       title="View Details">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Actions:</strong> 
                            <span class="badge bg-success small me-2">Pay</span> - Complete pending payment | 
                            <span class="badge bg-info small me-2">View</span> - View transaction details
                        </div>

                        <div class="mt-4 small">
                            {{ $transactions->links() }}
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('alumni.home') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .small {
        font-size: 0.875rem !important;
    }
    .table th, .table td {
        padding: 0.5rem !important;
    }
    .badge {
        font-size: 0.75rem !important;
        padding: 0.25em 0.6em !important;
    }
    .btn-sm {
        font-size: 0.875rem !important;
        padding: 0.25rem 0.5rem !important;
    }
    .pagination {
        font-size: 0.875rem !important;
    }
    .table td {
        vertical-align: middle !important;
    }
    .text-muted {
        color: #6c757d !important;
    }
</style>
@endsection 