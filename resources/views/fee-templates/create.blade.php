<x-alumniadmin-dashboard>
    <div class="container mt-5 pt-7" style="margin-left: 200px;">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0">Create New Fee</h6>
                    </div>
                    <div class="card-body p-4">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('fee-templates.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="graduation_year" class="form-label">Graduation Year</label>
                                <input type="number" name="graduation_year" id="graduation_year" class="form-control @error('graduation_year') is-invalid @enderror" value="{{ old('graduation_year', date('Y')) }}" min="1900" max="{{ date('Y') + 1 }}" required>
                                @error('graduation_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="fee_type_id" class="form-label">Fee Type</label>
                                <select name="fee_type_id" id="fee_type_id" class="form-select @error('fee_type_id') is-invalid @enderror" required>
                                    <option value="">Select Fee Type</option>
                                    @foreach($feeTypes as $feeType)
                                        <option value="{{ $feeType->id }}" {{ old('fee_type_id') == $feeType->id ? 'selected' : '' }}>
                                            {{ $feeType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fee_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Optional name for this fee template">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₦</span>
                                    <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="valid_from" class="form-label">Valid From</label>
                                    <input type="date" name="valid_from" id="valid_from" class="form-control @error('valid_from') is-invalid @enderror" value="{{ old('valid_from', date('Y-m-d')) }}" required>
                                    @error('valid_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="valid_until" class="form-label">Valid Until (Optional)</label>
                                    <input type="date" name="valid_until" id="valid_until" class="form-control @error('valid_until') is-invalid @enderror" value="{{ old('valid_until') }}">
                                    @error('valid_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label for="is_active" class="form-check-label">Active</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('fee-templates.index') }}" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Fee Template</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 