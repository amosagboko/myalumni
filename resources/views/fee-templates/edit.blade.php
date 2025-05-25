<x-alumniadmin-dashboard>
    <div class="container mt-5 pt-7" style="margin-left: 200px;">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0">Edit Fee</h6>
                    </div>
                    <div class="card-body p-4">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('fee-templates.update', $fee) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $fee->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="alumni_year_id" class="form-label">Alumni Year</label>
                                <select name="alumni_year_id" id="alumni_year_id" class="form-select @error('alumni_year_id') is-invalid @enderror" required>
                                    <option value="">Select Year</option>
                                    @foreach($alumniYears as $year)
                                        <option value="{{ $year->id }}" {{ old('alumni_year_id', $fee->alumni_year_id) == $year->id ? 'selected' : '' }}>
                                            {{ $year->year }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('alumni_year_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="fee_type" class="form-label">Fee Type</label>
                                <select name="fee_type" id="fee_type" class="form-select @error('fee_type') is-invalid @enderror" required>
                                    <option value="">Select Fee Type</option>
                                    @foreach($feeTypes as $feeType)
                                        <option value="{{ $feeType->code }}" {{ old('fee_type', $fee->feeType->code) == $feeType->code ? 'selected' : '' }}>
                                            {{ $feeType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fee_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₦</span>
                                    <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $fee->amount) }}" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $fee->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $fee->is_active) ? 'checked' : '' }}>
                                    <label for="is_active" class="form-check-label">Active</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_test_mode" id="is_test_mode" class="form-check-input" value="1" {{ old('is_test_mode', $fee->is_test_mode) ? 'checked' : '' }}>
                                    <label for="is_test_mode" class="form-check-label">Test Mode</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('fee-templates.index') }}" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Fee</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 