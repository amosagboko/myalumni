@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-5 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title h5 h-md-4 mb-0">Complete Your Payments</h3>
                </div>
                <div class="card-body">
                    @php
                        $duesPhase = $duesPhase ?? 'onboarding';
                        $paymentYearLabel = $activePaymentYear?->year;
                        $combinedCheckout = $combinedCheckout ?? null;
                    @endphp

                    <div class="mb-3 mb-md-4 text-muted">
                        @if($combinedCheckout)
                            Pay the items below as a single combined payment. Individual items cannot be paid separately while this combined option is active.
                        @elseif($duesPhase === 'onboarding')
                            Please complete all required onboarding payments for your graduation cohort before you can access full alumni services.
                        @elseif($duesPhase === 'annual')
                            Pay your annual alumni due for payment year {{ $paymentYearLabel }} to keep your membership active.
                        @else
                            There are no payments due on your account right now.
                        @endif
                    </div>

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

                    @if($combinedCheckout)
                        <div class="border rounded-3 p-3 p-md-4 mb-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <h4 class="h6 mb-1">{{ $combinedCheckout['structure']->payerTitle() }}</h4>
                                    <div class="text-muted small">Combined payment — {{ $combinedCheckout['fees']->count() }} items</div>
                                </div>
                                <div class="fw-semibold fs-5">₦{{ number_format($combinedCheckout['total'], 2) }}</div>
                            </div>
                            <div class="table-responsive mb-3">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($combinedCheckout['fees'] as $fee)
                                            <tr>
                                                <td>{{ $fee->description ?: $fee->feeType?->name }}</td>
                                                <td class="text-end">₦{{ number_format($fee->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <th class="text-end">₦{{ number_format($combinedCheckout['total'], 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <form action="{{ route('alumni.payments.initiate-combined') }}" method="POST">
                                @csrf
                                <input type="hidden" name="payment_structure_id" value="{{ $combinedCheckout['structure']->id }}">
                                <button type="submit" class="btn btn-primary">Pay Combined Total</button>
                            </form>
                        </div>
                    @endif

                    @php
                        $combinedIds = collect($combinedCheckout['fees'] ?? [])->pluck('id');
                        $separateFees = $fees->reject(fn ($fee) => $combinedIds->contains($fee->id))->values();
                    @endphp

                    @if($separateFees->isNotEmpty() || ! $combinedCheckout)
                    <!-- Desktop/tablet view -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fee Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($separateFees as $fee)
                                    <tr>
                                        <td>{{ $fee->description }}</td>
                                        <td>₦{{ number_format($fee->amount, 2) }}</td>
                                        <td>
                                            @if($fee->isPaid())
                                                <span class="badge bg-success">Paid</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$fee->isPaid())
                                                <form action="{{ route('alumni.payments.initiate') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="fee_id" value="{{ $fee->id }}">
                                                    <button type="submit" class="btn btn-sm btn-primary">Pay Now</button>
                                                </form>
                                            @else
                                                <a href="{{ route('alumni.payments.show', $fee->getCompletedTransaction()->id) }}" class="btn btn-sm btn-info">View Receipt</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No fees found for your profile.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile view: cards -->
                    <div class="d-md-none">
                        @forelse($separateFees as $fee)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold">{{ $fee->description }}</div>
                                            <div class="text-muted small">Amount: ₦{{ number_format($fee->amount, 2) }}</div>
                                        </div>
                                        <div>
                                            @if($fee->isPaid())
                                                <span class="badge bg-success">Paid</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        @if(!$fee->isPaid())
                                            <form action="{{ route('alumni.payments.initiate') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="fee_id" value="{{ $fee->id }}">
                                                <button type="submit" class="btn btn-primary w-100">Pay Now</button>
                                            </form>
                                        @else
                                            <a href="{{ route('alumni.payments.show', $fee->getCompletedTransaction()->id) }}" class="btn btn-info w-100">View Receipt</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted">No fees found for your profile.</div>
                        @endforelse
                    </div>
                    @endif

                    @if($duesPhase === 'onboarding' && $fees->isNotEmpty() && $fees->every->isPaid())
                        <div class="mt-4 text-center">
                            <a href="{{ route('alumni.home') }}" class="btn btn-success">
                                Complete Onboarding
                            </a>
                        </div>
                    @elseif($duesPhase === 'annual' && $fees->isNotEmpty() && $fees->every->isPaid())
                        <div class="mt-4 text-center">
                            <a href="{{ route('alumni.home') }}" class="btn btn-success">
                                Return to Dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 