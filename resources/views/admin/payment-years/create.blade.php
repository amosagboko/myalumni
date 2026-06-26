<x-alumniadmin-dashboard title="New Payment Year | FuLafia Alumni">
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Create Payment Year</h5>
                                <a href="{{ route('admin.payment-years.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.payment-years.store') }}" method="POST">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="year" class="form-label">Payment year</label>
                                        <input type="number" name="year" id="year" class="form-control @error('year') is-invalid @enderror"
                                            value="{{ old('year', $suggestedYear) }}" min="2000" max="2100" required>
                                        @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="start_date" class="form-label">Start date</label>
                                            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                                value="{{ old('start_date', $suggestedYear . '-01-01') }}" required>
                                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="end_date" class="form-label">End date</label>
                                            <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                                value="{{ old('end_date', $suggestedYear . '-12-31') }}" required>
                                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input"
                                                {{ old('is_active') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Set as active payment year immediately</label>
                                        </div>
                                    </div>

                                    @if($previousYear?->annualDueTemplate())
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="copy_annual_due_from_previous" id="copy_annual_due_from_previous" value="1"
                                                    class="form-check-input" {{ old('copy_annual_due_from_previous', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="copy_annual_due_from_previous">
                                                    Copy annual due amount from {{ $previousYear->year }}
                                                    (₦{{ number_format($previousYear->annualDueTemplate()->amount, 2) }})
                                                </label>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.payment-years.index') }}" class="btn btn-light">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Create payment year</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>
