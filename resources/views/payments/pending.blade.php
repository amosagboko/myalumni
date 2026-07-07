@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-5 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h3 class="card-title h5 mb-1">Payment processing</h3>
                    <p class="text-muted small mb-0">We are confirming your payment with the payment provider.</p>
                </div>
                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info">{{ session('info') }}</div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif

                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 mb-3"
                             style="width: 72px; height: 72px;">
                            <i class="fas fa-spinner fa-spin text-warning fa-2x"></i>
                        </div>
                        <h4 class="h5 fw-semibold mb-2">Your payment is being processed</h4>
                        <p class="text-muted mb-0">
                            This usually takes a few moments. If you completed payment on the checkout page,
                            you do not need to pay again.
                        </p>
                    </div>

                    <div class="alert alert-light border mb-4">
                        <p class="small mb-2 fw-semibold">What you can do now</p>
                        <ol class="small text-muted mb-0 ps-3">
                            <li class="mb-1">Wait about one minute for the payment provider to confirm your transaction.</li>
                            <li class="mb-1">Click <strong>Verify Payment Status</strong> below to check again.</li>
                            <li>If money was deducted but this page does not update, contact support with your payment reference.</li>
                        </ol>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th class="bg-light text-nowrap" style="width: 40%;">Payment</th>
                                    <td>{{ $transaction->display_description }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Payment reference</th>
                                    <td><code class="small">{{ $transaction->payment_reference }}</code></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Amount</th>
                                    <td class="fw-semibold">₦{{ number_format($transaction->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Date initiated</th>
                                    <td>{{ $transaction->created_at->format('M d, Y H:i A') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-grid gap-2 d-sm-flex">
                        <form
                            action="{{ route('alumni.payments.verify', $transaction) }}"
                            method="POST"
                            class="flex-grow-1"
                        >
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Verify Payment Status
                            </button>
                        </form>
                        <a href="{{ route('alumni.payments.index') }}" class="btn btn-outline-secondary">
                            Back to payments
                        </a>
                    </div>

                    <p class="text-muted small text-center mt-4 mb-0">
                        Keep your payment reference
                        <code class="small">{{ $transaction->payment_reference }}</code>
                        handy when contacting support.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
