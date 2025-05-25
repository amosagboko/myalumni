@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-5" style="margin-left: 150px; margin-top: 100px !important;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Complete Your Payments</h3>
                </div>
                <div class="card-body">
                    <div class="mb-4 text-muted">
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
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Fee Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
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
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        Pay Now
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('alumni.payments.show', $fee->getCompletedTransaction()->id) }}" class="btn btn-sm btn-info">
                                                    View Receipt
                                                </a>
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