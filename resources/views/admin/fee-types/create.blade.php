<x-alumniadmin-dashboard>
    <div class="container-fluid mt-5 pt-7" style="margin-left: 180px;">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Add New Fee Type</h1>
                    <a href="{{ route('admin.fee-types.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Fee Types
                    </a>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('admin.fee-types.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">A descriptive name for the fee type (e.g., "Registration Fee")</div>
                            </div>

                            <div class="mb-3">
                                <label for="code" class="form-label">Code</label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code') }}">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">A unique code for the fee type (e.g., "registration"). Leave blank to auto-generate from name.</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">A brief description of what this fee type is for.</div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input @error('is_active') is-invalid @enderror" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Active fee types can be used when creating new fees.</div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Fee Type
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 