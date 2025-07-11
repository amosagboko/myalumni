@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 h5 h-md-4">Payment History</h5>
                        <a href="{{ route('alumni.home') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            <span class="d-none d-sm-inline">Back to Dashboard</span>
                            <span class="d-inline d-sm-none">Back</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($transactions->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You have no payment history yet.
                        </div>
                    @else
                        @php
                            $pendingCount = $transactions->where('status', 'pending')->count();
                        @endphp
                        
                        @if($pendingCount > 0)
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                You have <strong>{{ $pendingCount }}</strong> pending payment{{ $pendingCount > 1 ? 's' : '' }}. 
                                Click the "Pay" button next to any pending transaction to complete your payment.
                            </div>
                        @endif

                        <!-- Mobile View - Cards -->
                        <div class="d-md-none">
                            @foreach($transactions as $transaction)
                                <div class="transaction-card mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $transaction->feeTemplate->feeType->name }}</h6>
                                            <p class="text-muted small mb-1">{{ $transaction->feeTemplate->graduation_year }}</p>
                                            <p class="text-muted small mb-0">{{ $transaction->payment_reference }}</p>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold mb-1">₦{{ number_format($transaction->amount, 2) }}</div>
                                            @if($transaction->status === 'paid')
                                                <span class="badge bg-success small">Paid</span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="badge bg-warning small">Pending</span>
                                            @else
                                                <span class="badge bg-danger small">Failed</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <small class="text-muted">
                                                {{ $transaction->created_at->format('M d, Y H:i A') }}
                                                <br>
                                                {{ $transaction->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div class="col-4 text-end">
                                            @if($transaction->status === 'pending')
                                                <a href="{{ route('alumni.payments.process', $transaction) }}" 
                                                   class="btn btn-success btn-sm w-100" 
                                                   title="Complete Payment">
                                                    <i class="fas fa-credit-card me-1"></i>
                                                    <span class="d-none d-sm-inline">Pay</span>
                                                    <span class="d-inline d-sm-none">Pay</span>
                                                </a>
                                            @elseif($transaction->status === 'paid')
                                                <a href="{{ route('alumni.payments.show', $transaction) }}" 
                                                   class="btn btn-info btn-sm w-100" 
                                                   title="View Details">
                                                    <i class="fas fa-eye me-1"></i>
                                                    <span class="d-none d-sm-inline">View</span>
                                                    <span class="d-inline d-sm-none">View</span>
                                                </a>
                                            @else
                                                <a href="{{ route('alumni.payments.show', $transaction) }}" 
                                                   class="btn btn-secondary btn-sm w-100" 
                                                   title="View Details">
                                                    <i class="fas fa-eye me-1"></i>
                                                    <span class="d-none d-sm-inline">View</span>
                                                    <span class="d-inline d-sm-none">View</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Desktop View - Table -->
                        <div class="d-none d-md-block">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Fee Type</th>
                                            <th>Year</th>
                                            <th>Amount</th>
                                            <th>Reference</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $transaction)
                                            <tr>
                                                <td>
                                                    {{ $transaction->created_at->format('M d, Y H:i A') }}
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $transaction->created_at->diffForHumans() }}
                                                    </small>
                                                </td>
                                                <td>{{ $transaction->feeTemplate->feeType->name }}</td>
                                                <td>{{ $transaction->feeTemplate->graduation_year }}</td>
                                                <td>₦{{ number_format($transaction->amount, 2) }}</td>
                                                <td>{{ $transaction->payment_reference }}</td>
                                                <td>
                                                    @if($transaction->status === 'paid')
                                                        <span class="badge bg-success">Paid</span>
                                                    @elseif($transaction->status === 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @else
                                                        <span class="badge bg-danger">Failed</span>
                                                    @endif
                                                </td>
                                                <td>
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
                        </div>

                        <div class="mt-3 text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Actions:</strong> 
                            <span class="badge bg-success small me-2">Pay</span> - Complete pending payment | 
                            <span class="badge bg-info small me-2">View</span> - View transaction details
                        </div>

                        <div class="mt-4">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.transaction-card {
    background: white;
    transition: all 0.3s ease;
}

.transaction-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container-fluid {
        margin-left: 0 !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .transaction-card {
        margin-bottom: 1rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.25em 0.6em;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .transaction-card {
        padding: 0.75rem !important;
        margin-bottom: 0.75rem;
    }
    
    .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.2em 0.5em;
    }
    
    .pagination {
        font-size: 0.875rem;
    }
}

@media (max-width: 480px) {
    .container-fluid {
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }
    
    .card-body {
        padding: 0.5rem;
    }
    
    .transaction-card {
        padding: 0.5rem !important;
        margin-bottom: 0.5rem;
    }
    
    .btn {
        font-size: 0.75rem;
        padding: 0.35rem 0.5rem;
    }
    
    .badge {
        font-size: 0.65rem;
        padding: 0.15em 0.4em;
    }
}

.table th, .table td {
    padding: 0.5rem;
    vertical-align: middle;
}

.text-muted {
    color: #6c757d;
}
</style>
@endsection 