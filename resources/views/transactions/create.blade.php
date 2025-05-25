@extends('components.alumniadmin-dashboard')

@section('content')
<div class="main-content bg-lightblue theme-dark-bg right-chat-active">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left">
            <div class="middle-wrap">
                <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                    <div class="card-body p-4 w-100 bg-current border-0 d-flex rounded-3">
                        <h2 class="fw-700 mb-0 mt-0 font-md text-white">Create Transaction</h2>
                        <a href="{{ route('transactions.index') }}" class="ms-auto text-white">
                            <i class="feather-arrow-left"></i> Back to List
                        </a>
                    </div>

                    <div class="card-body p-lg-5 p-4 w-100 border-0">
                        <form action="{{ route('transactions.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Current Alumni Year</label>
                                <input type="text" class="form-control" value="{{ $currentYear->year }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="fee_id" class="form-label">Select Fee</label>
                                <select name="fee_id" id="fee_id" class="form-select @error('fee_id') is-invalid @enderror" required>
                                    <option value="">Select a fee</option>
                                    @foreach($fees as $fee)
                                        <option value="{{ $fee->id }}" data-amount="{{ $fee->amount }}">
                                            {{ $fee->category->name }} - {{ $fee->getFormattedFeeType() }} (₦{{ number_format($fee->amount, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('fee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₦</span>
                                    <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" required readonly>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="payment_reference" class="form-label">Payment Reference</label>
                                <input type="text" name="payment_reference" id="payment_reference" class="form-control @error('payment_reference') is-invalid @enderror" required>
                                @error('payment_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-end">
                                <a href="{{ route('transactions.index') }}" class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-save me-1"></i> Create Transaction
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const feeSelect = document.getElementById('fee_id');
    const amountInput = document.getElementById('amount');

    feeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const amount = selectedOption.dataset.amount;
        amountInput.value = amount;
    });
});
</script>
@endpush
@endsection 