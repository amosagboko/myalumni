@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-5" style="margin-left: 150px; margin-top: 100px !important;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Payment Processing</h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-spinner fa-spin text-warning" style="font-size: 4rem;"></i>
                    </div>

                    <h4 class="mb-3">Your Payment is Being Processed</h4>
                    
                    <div class="alert alert-info">
                        We are currently verifying your payment. This may take a few moments.
                    </div>

                    <div class="table-responsive mb-4">
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
                                <th>Date</th>
                                <td>{{ $transaction->created_at->format('M d, Y H:i A') }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-grid gap-2">
                        <form action="{{ route('alumni.payments.verify', $transaction) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                Verify Payment Status
                            </button>
                        </form>
                        <a href="{{ route('alumni.payments.index') }}" class="btn btn-outline-secondary">
                            Back to Payments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 