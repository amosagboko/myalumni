@extends('layouts.elcom')

@section('content')
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Create New Office</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('elcom.election-offices.store', $election) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Office Title</label>
                            <input type="text" 
                                class="form-control @error('title') is-invalid @enderror" 
                                id="title" 
                                name="title" 
                                value="{{ old('title') }}" 
                                required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" 
                                name="description" 
                                rows="3" 
                                required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="max_candidates" class="form-label">Maximum Candidates</label>
                            <input type="number" 
                                class="form-control @error('max_candidates') is-invalid @enderror" 
                                id="max_candidates" 
                                name="max_candidates" 
                                value="{{ old('max_candidates', 1) }}" 
                                min="1" 
                                required>
                            @error('max_candidates')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="max_terms" class="form-label">Maximum Terms</label>
                            <input type="number" 
                                class="form-control @error('max_terms') is-invalid @enderror" 
                                id="max_terms" 
                                name="max_terms" 
                                value="{{ old('max_terms', 1) }}" 
                                min="1" 
                                required>
                            @error('max_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fee_type_id" class="form-label">Screening Fee Type</label>
                            <select class="form-select @error('fee_type_id') is-invalid @enderror" 
                                id="fee_type_id" 
                                name="fee_type_id" 
                                required>
                                <option value="">Select a fee type</option>
                                @foreach($feeTypes as $feeType)
                                    <option value="{{ $feeType->id }}" {{ old('fee_type_id') == $feeType->id ? 'selected' : '' }}>
                                        {{ $feeType->name }} ({{ $feeType->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('fee_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This fee type will be used to determine the screening fee for candidates.</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('elcom.elections.edit', $election) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Office</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 