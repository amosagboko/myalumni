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
                    <div class="mb-3 mb-md-4 text-muted">
                        Please complete all required payments to finish your onboarding process.
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

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Fee Description</th>
                                    <th class="text-nowrap">Amount</th>
                                    <th class="text-nowrap">Status</th>
                                    <th class="text-nowrap">Actions</th>
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
                                                    <button type="submit" class="btn btn-sm btn-primary text-nowrap">Pay Now</button>
                                                </form>
                                            @else
                                                <a href="{{ route('alumni.payments.show', $fee->getCompletedTransaction()->id) }}" class="btn btn-sm btn-info text-nowrap">View Receipt</a>
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

                    @if($fees->isNotEmpty() && $fees->every->isPaid())
                        <div class="mt-4 text-center">
                            <a href="{{ route('alumni.home') }}" class="btn btn-success">
                                Complete Onboarding
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 