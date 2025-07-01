@extends('layouts.elcom')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Transaction Management</h4>
                    <div>
                        <a href="{{ route('elcom.elections.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Elections
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Statistics Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Transaction Statistics</h5>
                        </div>
                        
                        <!-- User Statistics -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title text-white">Total Users</h6>
                                    <h3 class="mb-0 text-white">{{ number_format($totalUsers) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title text-white">Total Onboarded Users</h6>
                                    <h3 class="mb-0 text-white">{{ number_format($totalOnboardedUsers) }}</h3>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Transaction Statistics -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title text-white">Total Transactions</h6>
                                    <h3 class="mb-0 text-white">{{ number_format($transactionStats['total_transactions']) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title text-white">Paid Transactions</h6>
                                    <h3 class="mb-0 text-white">{{ number_format($transactionStats['paid_transactions']) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title text-white">Pending Transactions</h6>
                                    <h3 class="mb-0 text-white">{{ number_format($transactionStats['pending_transactions']) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title text-white">Failed Transactions</h6>
                                    <h3 class="mb-0 text-white">{{ number_format($transactionStats['failed_transactions']) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-dark text-white">
                                <div class="card-body">
                                    <h6 class="card-title text-white">Total Amount Paid</h6>
                                    <h3 class="mb-0 text-white">₦{{ number_format($transactionStats['total_amount_paid'], 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Recent Transactions Section -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">Recent Transactions</h5>
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Alumni</th>
                                                    <th>Fee Type</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Payment Reference</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentTransactions as $transaction)
                                                    <tr>
                                                        <td>
                                                            <small>
                                                                {{ $transaction->created_at->format('M d, Y') }}
                                                                <br>
                                                                <span class="text-muted">{{ $transaction->created_at->format('H:i A') }}</span>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            @if($transaction->alumni && $transaction->alumni->user)
                                                                {{ $transaction->alumni->user->name }}
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($transaction->feeTemplate && $transaction->feeTemplate->feeType)
                                                                {{ $transaction->feeTemplate->feeType->name }}
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>₦{{ number_format($transaction->amount, 2) }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $transaction->status === 'paid' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                                                {{ ucfirst($transaction->status) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">{{ $transaction->payment_reference ?? 'N/A' }}</small>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4">
                                                            <div class="text-muted">
                                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                                <br>
                                                                No transactions found
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Transaction Overview</h6>
                                <p class="mb-0">
                                    This page shows all transactions across the system, including subscription fees and election/EOI fees. 
                                    The statistics provide a comprehensive view of payment activities and user onboarding status.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 