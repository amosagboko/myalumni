@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 h5 h-md-4">Payment Details</h5>
                        <a href="{{ route('alumni.payments.history') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            <span class="d-none d-sm-inline">Back to History</span>
                            <span class="d-inline d-sm-none">Back</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Mobile-friendly cards layout -->
                    <div class="d-block d-md-none">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-1 text-muted small">Payment Reference</h6>
                                        <p class="card-text fw-bold">{{ $transaction->payment_reference }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-1 text-muted small">Amount</h6>
                                        <p class="card-text fw-bold text-success">₦{{ number_format($transaction->amount, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-1 text-muted small">Status</h6>
                                        <p class="card-text">
                                            @if($transaction->status === 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-1 text-muted small">Date</h6>
                                        <p class="card-text">{{ $transaction->created_at->format('M d, Y H:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                            @if($transaction->paid_at)
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-1 text-muted small">Paid At</h6>
                                        <p class="card-text">{{ $transaction->paid_at->format('M d, Y H:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Desktop table layout -->
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th class="w-25">Payment Reference</th>
                                    <td>{{ $transaction->payment_reference }}</td>
                                </tr>
                                <tr>
                                    <th>Amount</th>
                                    <td class="text-success fw-bold">₦{{ number_format($transaction->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($transaction->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td>{{ $transaction->created_at->format('M d, Y H:i A') }}</td>
                                </tr>
                                @if($transaction->paid_at)
                                <tr>
                                    <th>Paid At</th>
                                    <td>{{ $transaction->paid_at->format('M d, Y H:i A') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($transaction->status === 'pending')
                        <div class="mt-4 text-center text-md-start">
                            <a href="{{ route('alumni.payments.process', $transaction) }}" class="btn btn-success btn-lg w-100 w-md-auto">
                                <i class="fas fa-credit-card me-2"></i>
                                Proceed to Payment
                            </a>
                        </div>
                    @endif

                    <div class="mt-4 text-center text-md-start">
                        <a href="{{ route('alumni.payments.history') }}" class="btn btn-secondary w-100 w-md-auto">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Payment History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 767.98px) {
    .h-md-4 {
        font-size: 1.25rem !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .table-responsive {
        border: none;
    }
    
    .card.border-0.bg-light {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
}

@media (min-width: 768px) {
    .w-md-auto {
        width: auto !important;
    }
    
    .text-md-start {
        text-align: left !important;
    }
}
</style>
@endsection 