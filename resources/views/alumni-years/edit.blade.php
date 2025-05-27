<x-alumniadmin-dashboard>
    <div class="main-content-body pt-7 mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-12">
                <div class="card mx-auto" style="max-width: 700px;">
                    <div class="card-header py-2">
                        <h4 class="card-title mb-0">Edit Alumni Year</h4>
                    </div>
                    <div class="card-body p-3">
                        <form action="{{ route('alumni-years.update', $alumniYear) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-2">
                                <label for="year" class="form-label">Year</label>
                                <input type="number" name="year" id="year" value="{{ old('year', $alumniYear->year) }}" 
                                    class="form-control form-control-sm @error('year') is-invalid @enderror"
                                    required min="1900" max="{{ date('Y') + 1 }}">
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-2">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $alumniYear->start_date->format('Y-m-d')) }}"
                                    class="form-control form-control-sm @error('start_date') is-invalid @enderror"
                                    required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-2">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $alumniYear->end_date->format('Y-m-d')) }}"
                                    class="form-control form-control-sm @error('end_date') is-invalid @enderror"
                                    required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $alumniYear->is_active) ? 'checked' : '' }}
                                        class="form-check-input">
                                    <label class="form-check-label" for="is_active">Set as active year</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('alumni-years.index') }}" class="btn btn-secondary btn-sm me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-sm">Update Year</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 