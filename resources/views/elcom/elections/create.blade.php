@extends('layouts.elcom')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Create New Election</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('elcom.elections.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Election Title</label>
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
                                    <label for="eligibility_criteria" class="form-label">Eligibility Criteria</label>
                                    <textarea class="form-control @error('eligibility_criteria') is-invalid @enderror" 
                                        id="eligibility_criteria" 
                                        name="eligibility_criteria" 
                                        rows="3" 
                                        required>{{ old('eligibility_criteria') }}</textarea>
                                    @error('eligibility_criteria')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Voting Period</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="voting_start" class="form-label">Start Date & Time</label>
                                            <input type="datetime-local" 
                                                class="form-control @error('voting_start') is-invalid @enderror" 
                                                id="voting_start" 
                                                name="voting_start" 
                                                value="{{ old('voting_start') }}" 
                                                required>
                                            @error('voting_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="voting_end" class="form-label">End Date & Time</label>
                                            <input type="datetime-local" 
                                                class="form-control @error('voting_end') is-invalid @enderror" 
                                                id="voting_end" 
                                                name="voting_end" 
                                                value="{{ old('voting_end') }}" 
                                                required>
                                            @error('voting_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Expression of Interest Period</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="eoi_start" class="form-label">Start Date & Time</label>
                                            <input type="datetime-local" 
                                                class="form-control @error('eoi_start') is-invalid @enderror" 
                                                id="eoi_start" 
                                                name="eoi_start" 
                                                value="{{ old('eoi_start') }}" 
                                                required>
                                            @error('eoi_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="eoi_end" class="form-label">End Date & Time</label>
                                            <input type="datetime-local" 
                                                class="form-control @error('eoi_end') is-invalid @enderror" 
                                                id="eoi_end" 
                                                name="eoi_end" 
                                                value="{{ old('eoi_end') }}" 
                                                required>
                                            @error('eoi_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Accreditation Period</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="accreditation_start" class="form-label">Start Date & Time</label>
                                            <input type="datetime-local" 
                                                class="form-control @error('accreditation_start') is-invalid @enderror" 
                                                id="accreditation_start" 
                                                name="accreditation_start" 
                                                value="{{ old('accreditation_start') }}" 
                                                required>
                                            @error('accreditation_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="accreditation_end" class="form-label">End Date & Time</label>
                                            <input type="datetime-local" 
                                                class="form-control @error('accreditation_end') is-invalid @enderror" 
                                                id="accreditation_end" 
                                                name="accreditation_end" 
                                                value="{{ old('accreditation_end') }}" 
                                                required>
                                            @error('accreditation_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Election Offices</h5>
                                        <button type="button" class="btn btn-sm btn-primary" id="addOffice">
                                            <i class="fas fa-plus me-1"></i> Add Office
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div id="offices-container">
                                            <!-- Office fields will be added here dynamically -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Create Election
                            </button>
                            <a href="{{ route('elcom.elections.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const officesContainer = document.getElementById('offices-container');
    const addOfficeBtn = document.getElementById('addOffice');
    let officeCount = 0;

    function createOfficeFields() {
        const officeDiv = document.createElement('div');
        officeDiv.className = 'office-fields mb-3 p-3 border rounded';
        officeDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Office #${officeCount + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger remove-office">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Office Title</label>
                        <input type="text" 
                            class="form-control" 
                            name="offices[${officeCount}][title]" 
                            required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" 
                            class="form-control" 
                            name="offices[${officeCount}][description]" 
                            required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Maximum Candidates</label>
                        <input type="number" 
                            class="form-control" 
                            name="offices[${officeCount}][max_candidates]" 
                            min="1" 
                            value="1" 
                            required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Term Duration (in years)</label>
                        <input type="number" 
                            class="form-control" 
                            name="offices[${officeCount}][term_duration]" 
                            min="1" 
                            value="1" 
                            required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Screening Fee Type</label>
                        <select class="form-select" 
                            name="offices[${officeCount}][fee_type_id]" 
                            required>
                            <option value="">Select a fee type</option>
                            @foreach($feeTypes as $feeType)
                                <option value="{{ $feeType->id }}">
                                    {{ $feeType->name }} ({{ $feeType->code }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">This fee type will be used to determine the screening fee for candidates.</div>
                    </div>
                </div>
            </div>
        `;

        officesContainer.appendChild(officeDiv);
        officeCount++;

        // Add event listener to remove button
        officeDiv.querySelector('.remove-office').addEventListener('click', function() {
            officeDiv.remove();
        });
    }

    // Add first office field by default
    createOfficeFields();

    // Add office button click handler
    addOfficeBtn.addEventListener('click', createOfficeFields);

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const eoiStart = new Date(document.getElementById('eoi_start').value);
        const eoiEnd = new Date(document.getElementById('eoi_end').value);
        const accreditationStart = new Date(document.getElementById('accreditation_start').value);
        const accreditationEnd = new Date(document.getElementById('accreditation_end').value);
        const votingStart = new Date(document.getElementById('voting_start').value);
        const votingEnd = new Date(document.getElementById('voting_end').value);

        // EOI validation
        if (eoiEnd <= eoiStart) {
            e.preventDefault();
            alert('Expression of Interest end time must be after start time');
            return;
        }

        // Accreditation must start after EOI ends
        if (accreditationStart <= eoiEnd) {
            e.preventDefault();
            alert('Accreditation period must start after Expression of Interest period ends');
            return;
        }

        // Accreditation validation
        if (accreditationEnd <= accreditationStart) {
            e.preventDefault();
            alert('Accreditation end time must be after start time');
            return;
        }

        // Voting must start after accreditation ends
        if (votingStart <= accreditationEnd) {
            e.preventDefault();
            alert('Voting period must start after Accreditation period ends');
            return;
        }

        // Voting validation
        if (votingEnd <= votingStart) {
            e.preventDefault();
            alert('Voting end time must be after start time');
            return;
        }

        if (officeCount === 0) {
            e.preventDefault();
            alert('Please add at least one office');
            return;
        }
    });
});
</script>
@endpush 