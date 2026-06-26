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
                    @endphp

                    <div class="mb-3 mb-md-4 text-muted">
                        @if($duesPhase === 'onboarding')
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
                                @forelse($fees as $fee)
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
                        @forelse($fees as $fee)
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