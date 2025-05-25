@extends('components.alumniadmin-dashboard')

@section('content')
<div class="main-content bg-lightblue theme-dark-bg right-chat-active">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left">
            <div class="middle-wrap">
                <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                    <div class="card-body p-4 w-100 bg-current border-0 d-flex rounded-3">
                        <h2 class="fw-700 mb-0 mt-0 font-md text-white">Transaction Details</h2>
                        <a href="{{ route('transactions.index') }}" class="ms-auto text-white">
                            <i class="feather-arrow-left"></i> Back to List
                        </a>
                    </div>

                    <div class="card-body p-lg-5 p-4 w-100 border-0">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Transaction Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-4 fw-bold">Date:</div>
                                            <div class="col-md-8">{{ $transaction->created_at->format('F d, Y H:i:s') }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4 fw-bold">User:</div>
                                            <div class="col-md-8">{{ $transaction->user->name }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4 fw-bold">Fee Type:</div>
                                            <div class="col-md-8">{{ $transaction->categoryTransactionFee->getFormattedFeeType() }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4 fw-bold">Category:</div>
                                            <div class="col-md-8">{{ $transaction->categoryTransactionFee->category->name }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4 fw-bold">Alumni Year:</div>
                                            <div class="col-md-8">{{ $transaction->categoryTransactionFee->alumniYear->year }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4 fw-bold">Amount:</div>
                                            <div class="col-md-8">₦{{ number_format($transaction->amount, 2) }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4 fw-bold">Payment Reference:</div>
                                            <div class="col-md-8">{{ $transaction->payment_reference }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4 fw-bold">Status:</div>
                                            <div class="col-md-8">
                                                <span class="badge {{ $transaction->status === 'paid' ? 'bg-success' : 'bg-warning' }}">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            </div>
                                        </div>
                                        @if($transaction->paid_at)
                                            <div class="row mb-3">
                                                <div class="col-md-4 fw-bold">Paid At:</div>
                                                <div class="col-md-8">{{ $transaction->paid_at->format('F d, Y H:i:s') }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Actions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            @if($transaction->status === 'pending' && Auth::user()->isAdmin())
                                                <form action="{{ route('transactions.verify', $transaction) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="feather-check me-1"></i> Verify Payment
                                                    </button>
                                                </form>
                                                <form action="{{ route('transactions.reject', $transaction) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger w-100">
                                                        <i class="feather-x me-1"></i> Reject Payment
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 