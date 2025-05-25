@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-5" style="margin-left: 150px; margin-top: 100px !important;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Confirm Payment</h3>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h4 class="h5 mb-3">Payment Details</h4>
                        <p>Please review your payment details before proceeding:</p>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th>Amount:</th>
                                    <td>₦{{ number_format($transaction->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Fee Type:</th>
                                    <td>{{ $transaction->categoryTransactionFee->feeType->name }}</td>
                                </tr>
                                <tr>
                                    <th>Reference:</th>
                                    <td>{{ $transaction->payment_reference }}</td>
                                </tr>
                            </table>
                        </div>

                        <form action="{{ route('alumni.payments.confirm', $transaction) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                Proceed to Payment
                            </button>
                            <a href="{{ route('alumni.payments.index') }}" class="btn btn-link">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 