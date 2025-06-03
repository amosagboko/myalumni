@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-5" style="margin-left: 150px; margin-top: 100px !important;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Payment Details</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th>Payment Reference</th>
                                <td>{{ $transaction->payment_reference }}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td>₦{{ number_format($transaction->amount, 2) }}</td>
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

                    @if($transaction->status === 'pending')
                        <div class="mt-4">
                            <a href="{{ route('alumni.payments.process', $transaction) }}" class="btn btn-success">
                                Proceed to Payment
                            </a>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('alumni.payments.index') }}" class="btn btn-secondary">
                            Back to Payments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 