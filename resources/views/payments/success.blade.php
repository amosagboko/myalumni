@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-5" style="margin-left: 150px; margin-top: 100px !important;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Payment Successful</h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>

                    <h4 class="mb-3">Thank you for your payment!</h4>
                    
                    <div class="alert alert-success">
                        Your payment has been processed successfully.
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
                                <td>{{ $transaction->paid_at->format('M d, Y H:i A') }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('alumni.home') }}" class="btn btn-primary">
                            Go to Dashboard
                        </a>
                        <a href="{{ route('alumni.payments.history') }}" class="btn btn-outline-secondary">
                            View Payment History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 