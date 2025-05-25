@extends('layouts.elcom')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Election</h4>
                    <a href="{{ route('elcom.elections.index') }}" class="btn btn-secondary btn-sm">Back to Elections</a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('elcom.elections.update', $election) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="title" class="form-label">Election Title</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $election->title) }}">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $election->description) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="eligibility_criteria" class="form-label">Eligibility Criteria</label>
                            <textarea name="eligibility_criteria" id="eligibility_criteria" class="form-control" rows="2">{{ old('eligibility_criteria', $election->eligibility_criteria) }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eoi_start" class="form-label">Expression of Interest Start</label>
                                <input type="datetime-local" name="eoi_start" id="eoi_start" class="form-control" value="{{ old('eoi_start', $election->eoi_start?->format('Y-m-d\\TH:i')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="eoi_end" class="form-label">Expression of Interest End</label>
                                <input type="datetime-local" name="eoi_end" id="eoi_end" class="form-control" value="{{ old('eoi_end', $election->eoi_end?->format('Y-m-d\\TH:i')) }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="accreditation_start" class="form-label">Accreditation Start</label>
                                <input type="datetime-local" name="accreditation_start" id="accreditation_start" class="form-control" value="{{ old('accreditation_start', $election->accreditation_start?->format('Y-m-d\\TH:i')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="accreditation_end" class="form-label">Accreditation End</label>
                                <input type="datetime-local" name="accreditation_end" id="accreditation_end" class="form-control" value="{{ old('accreditation_end', $election->accreditation_end?->format('Y-m-d\\TH:i')) }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="voting_start" class="form-label">Voting Start</label>
                                <input type="datetime-local" name="voting_start" id="voting_start" class="form-control" value="{{ old('voting_start', $election->voting_start?->format('Y-m-d\\TH:i')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="voting_end" class="form-label">Voting End</label>
                                <input type="datetime-local" name="voting_end" id="voting_end" class="form-control" value="{{ old('voting_end', $election->voting_end?->format('Y-m-d\\TH:i')) }}">
                            </div>
                        </div>

                        <hr>
                        <h5>Election Offices</h5>
                        @forelse($election->offices as $office)
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>{{ $office->title }}</span>
                                    <a href="{{ route('elcom.election-offices.edit', [$election, $office]) }}" class="btn btn-sm btn-outline-primary">Edit Office</a>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Description:</strong> {{ $office->description }}</p>
                                    <p class="mb-1"><strong>Max Candidates:</strong> {{ $office->max_candidates }}</p>
                                    <p class="mb-1"><strong>Term Duration:</strong> {{ $office->term_duration }} years</p>
                                    <a href="{{ route('elcom.election-offices.candidates.index', [$election, $office]) }}" class="btn btn-sm btn-info mt-2">Manage Candidates</a>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">No offices have been created for this election.</div>
                        @endforelse
                        <div class="mb-3">
                            <a href="{{ route('elcom.election-offices.create', $election) }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-plus"></i> Add New Office
                            </a>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Election</button>
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

        // Voting validation - must be on the same day
        const isSameDay = votingStart.toDateString() === votingEnd.toDateString();
        if (!isSameDay) {
            e.preventDefault();
            alert('Voting must start and end on the same day');
            return;
        }

        // Compare voting times
        const startTime = votingStart.getHours() * 3600 + votingStart.getMinutes() * 60 + votingStart.getSeconds();
        const endTime = votingEnd.getHours() * 3600 + votingEnd.getMinutes() * 60 + votingEnd.getSeconds();
        
        if (endTime <= startTime) {
            e.preventDefault();
            alert('Voting end time must be after start time');
            return;
        }
    });
});
</script>
@endpush 