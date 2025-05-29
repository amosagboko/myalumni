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
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">Date</th>
                                        <th class="small">Fee Type</th>
                                        <th class="small">Category</th>
                                        <th class="small">Year</th>
                                        <th class="small">Amount</th>
                                        <th class="small">Reference</th>
                                        <th class="small">Status</th>
                                        <th class="small">Paid At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td class="small">{{ $transaction->created_at->format('M d, Y H:i A') }}</td>
                                            <td class="small">{{ $transaction->feeTemplate->feeType->name }}</td>
                                            <td class="small">{{ $transaction->feeTemplate->category->name ?? 'N/A' }}</td>
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
                                            <td class="small">{{ $transaction->paid_at ? $transaction->paid_at->format('M d, Y H:i A') : 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 small">
                            {{ $transactions->links() }}
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('alumni.payments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Payments
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
    }
    .pagination {
        font-size: 0.875rem !important;
    }
</style>
@endsection 