<form action="{{ route('admin.payment-years.annual-due.store', $paymentYear) }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Amount (₦)</label>
            <input type="number" name="amount" step="0.01" min="0" class="form-control"
                value="{{ old('amount', $previousAnnualDue?->amount ?? '') }}" required
                placeholder="{{ $previousAnnualDue ? number_format($previousAnnualDue->amount, 2) : 'e.g. 4000' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Valid from</label>
            <input type="date" name="valid_from" class="form-control"
                value="{{ old('valid_from', $paymentYear->start_date->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Valid until</label>
            <input type="date" name="valid_until" class="form-control"
                value="{{ old('valid_until', $paymentYear->end_date->format('Y-m-d')) }}">
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control"
                value="{{ old('description', "Annual alumni due for {$paymentYear->year}") }}">
        </div>
        <div class="col-12">
            <div class="form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="new_annual_active_{{ $paymentYear->id }}" checked>
                <label class="form-check-label" for="new_annual_active_{{ $paymentYear->id }}">Active</label>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm mt-3">Create annual due</button>
</form>
