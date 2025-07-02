@extends('layouts.elcom')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('elcom.elections.index') }}">ELCOM</a></li>
                        <li class="breadcrumb-item active">Transaction Management</li>
                    </ol>
                </div>
                <h4 class="page-title">Transaction Management</h4>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- 1. Total Uploaded Users -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-white fw-normal mt-0" title="Total Uploaded Users">Total Uploaded Users</h5>
                            <h3 class="mt-3 mb-3 text-white">{{ number_format($totalUploadedUsers) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white rounded">
                                <i class="fe-users font-20 text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Subscribed Users -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-white fw-normal mt-0" title="Total Subscribed Users">Total Subscribed Users</h5>
                            <h3 class="mt-3 mb-3 text-white">{{ number_format($totalSubscribedUsers) }}</h3>
                            <p class="text-white-50 mb-0">₦{{ number_format($totalSubscribedAmount, 2) }}</p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white rounded">
                                <i class="fe-check-circle font-20 text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total EOI -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-white fw-normal mt-0" title="Total EOI">Total EOI</h5>
                            <h3 class="mt-3 mb-3 text-white">{{ number_format($totalEOI) }}</h3>
                            <p class="text-white-50 mb-0">₦{{ number_format($totalEOIAmount, 2) }}</p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white rounded">
                                <i class="fe-file-text font-20 text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Total Transactions -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-white fw-normal mt-0" title="Total Transactions">Total Transactions</h5>
                            <h3 class="mt-3 mb-3 text-white">{{ number_format($totalTransactions) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white rounded">
                                <i class="fe-credit-card font-20 text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- 5. Paid Transactions -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-white fw-normal mt-0" title="Paid Transactions">Paid Transactions</h5>
                            <h3 class="mt-3 mb-3 text-white">{{ number_format($paidTransactions) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white rounded">
                                <i class="fe-check font-20 text-secondary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Pending Transactions -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-white fw-normal mt-0" title="Pending Transactions">Pending Transactions</h5>
                            <h3 class="mt-3 mb-3 text-white">{{ number_format($pendingTransactions) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white rounded">
                                <i class="fe-clock font-20 text-dark"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Failed Transactions -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-white fw-normal mt-0" title="Failed Transactions">Failed Transactions</h5>
                            <h3 class="mt-3 mb-3 text-white">{{ number_format($failedTransactions) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white rounded">
                                <i class="fe-x font-20 text-danger"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. Total Amount Paid -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-white fw-normal mt-0" title="Total Amount Paid">Total Amount Paid</h5>
                            <h3 class="mt-3 mb-3 text-white">₦{{ number_format($totalAmountPaid, 2) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white rounded">
                                <i class="fe-dollar-sign font-20 text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 9. Special Exemption -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-purple text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-white fw-normal mt-0" title="Special Exemption">Special Exemption</h5>
                            <h3 class="mt-3 mb-3 text-white">{{ number_format($specialExemption) }}</h3>
                            <p class="text-white-50 mb-0">2024 Graduates</p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-white rounded">
                                <i class="fe-award font-20 text-purple"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Recent Transactions</h4>
                    
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Alumni</th>
                                    <th>Fee Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td>
                                        <span class="text-body fw-bold">{{ $transaction->transaction_id }}</span>
                                    </td>
                                    <td>
                                        @if($transaction->alumni && $transaction->alumni->user)
                                            <span class="text-body fw-bold">{{ $transaction->alumni->user->name }}</span>
                                            <br>
                                            <small class="text-muted">{{ $transaction->alumni->user->email }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->feeTemplate && $transaction->feeTemplate->feeType)
                                            <span class="text-body">{{ $transaction->feeTemplate->feeType->name }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-body fw-bold">₦{{ number_format($transaction->amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($transaction->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($transaction->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($transaction->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $transaction->created_at->format('M d, Y H:i') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No transactions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $recentTransactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 