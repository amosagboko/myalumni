@extends('components.alumniadmin-dashboard')

@section('content')
<div class="main-content bg-lightblue theme-dark-bg right-chat-active">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left">
            <div class="middle-wrap">
                <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                    <div class="card-body p-4 w-100 bg-current border-0 d-flex rounded-3">
                        <h2 class="fw-700 mb-0 mt-0 font-md text-white">Transactions</h2>
                        @can('create transactions')
                            <a href="{{ route('transactions.create') }}" class="ms-auto text-white">
                                <i class="ti-plus"></i> New Transaction
                            </a>
                        @endcan
                    </div>

                    <div class="card-body p-lg-5 p-4 w-100 border-0">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($transactions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>User</th>
                                            <th>Fee Type</th>
                                            <th>Category</th>
                                            <th>Year</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                                <td>{{ $transaction->user->name }}</td>
                                                <td>{{ $transaction->feeTemplate->feeType->name }}</td>
                                                <td>{{ $transaction->feeTemplate->category->name }}</td>
                                                <td>{{ $transaction->feeTemplate->graduation_year }}</td>
                                                <td>₦{{ number_format($transaction->amount, 2) }}</td>
                                                <td>
                                                    <span class="badge {{ $transaction->status === 'paid' ? 'bg-success' : 'bg-warning' }}">
                                                        {{ ucfirst($transaction->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-info">
                                                            View
                                                        </a>
                                                        @if($transaction->status === 'pending' && Auth::user()->isAdmin())
                                                            <form action="{{ route('transactions.verify', $transaction) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success">
                                                                    Verify
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('transactions.reject', $transaction) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    Reject
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">No transactions found.</p>
                                @can('create transactions')
                                    <a href="{{ route('transactions.create') }}" class="btn btn-primary mt-3">
                                        <i class="feather-plus me-1"></i> Create Your First Transaction
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 