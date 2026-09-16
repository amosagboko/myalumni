@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-5" style="margin-left: 150px; margin-top: 100px !important;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Payment Failed</h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>

                    <h4 class="mb-3">Payment Verification Failed</h4>
                    
                    <div class="alert alert-danger">
                        We were unable to verify your payment. This could be due to:
                        <ul class="mb-0 mt-2 text-start">
                            <li>The payment was not completed successfully</li>
                            <li>The payment is still being processed by the payment provider</li>
                            <li>There was an issue with the payment verification</li>
                        </ul>
                    </div>

                    @include('payments.partials.transaction-summary', [
                        'transaction' => $transaction,
                        'dateLabel' => 'Date initiated',
                        'dateValue' => $transaction->created_at,
                        'showStatus' => true,
                    ])

                    <div class="d-grid gap-2">
                        <a href="{{ route('alumni.payments.index') }}" class="btn btn-primary">
                            Try Payment Again
                        </a>
                        <a href="{{ route('alumni.home') }}" class="btn btn-outline-secondary">
                            Return to Dashboard
                        </a>
                    </div>

                    <div class="mt-4">
                        <p class="text-muted">
                            If you believe this is an error or have already made the payment, 
                            please contact support with your payment reference.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 