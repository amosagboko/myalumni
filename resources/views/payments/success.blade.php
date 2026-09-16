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

                    @if($eoiApplication)
                        <div class="alert alert-info text-start mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-credit-card fs-4 mt-1"></i>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading h6 mb-2">Expression of Interest payment received</h5>
                                    <p class="mb-2">
                                        Your screening fee has been paid. Your application is now
                                        <strong>{{ $eoiApplication['status_label'] }}</strong>.
                                    </p>
                                    @if($eoiApplication['office'] || $eoiApplication['election'])
                                        <p class="mb-0 small text-muted">
                                            @if($eoiApplication['office'])
                                                <span class="d-block"><strong>Office:</strong> {{ $eoiApplication['office'] }}</span>
                                            @endif
                                            @if($eoiApplication['election'])
                                                <span class="d-block"><strong>Election:</strong> {{ $eoiApplication['election'] }}</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <tr>
                                <th>Payment Reference</th>
                                <td>{{ $transaction->payment_reference }}</td>
                            </tr>
                            <tr>
                                <th>Payment</th>
                                <td>{{ $transaction->display_description }}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td>₦{{ number_format($transaction->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ $transaction->paid_at?->format('M d, Y H:i A') ?? now()->format('M d, Y H:i A') }}</td>
                            </tr>
                        </table>
                    </div>

                    @if($transaction->isCombined() && $transaction->items->isNotEmpty())
                        <div class="table-responsive mb-4 text-start">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transaction->items as $item)
                                        <tr>
                                            <td>{{ $item->description }}</td>
                                            <td class="text-end">₦{{ number_format($item->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-end">₦{{ number_format($transaction->amount, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        @if($eoiApplication)
                            <a href="{{ route('alumni.elections.expression-of-interest.status') }}" class="btn btn-primary">
                                <i class="bi bi-clipboard-check me-1"></i>
                                View EOI Status
                            </a>
                            <a href="{{ route('alumni.elections') }}" class="btn btn-outline-primary">
                                Go to Elections
                            </a>
                        @else
                            <a href="{{ route('alumni.home') }}" class="btn btn-primary">
                                Go to Dashboard
                            </a>
                        @endif
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