@extends('layouts.elcom')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Start New Election Cycle</h4>
                    <a href="{{ route('elcom.elections.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="alert alert-info">
                        This wizard creates a new election year. Office structure can be cloned from an archived election.
                        Candidates, votes, accreditation, and results are never copied.
                    </div>

                    <form action="{{ route('elcom.elections.new-cycle.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="clone_from_election_id" class="form-label">Clone Offices From (optional)</label>
                                    <select class="form-select @error('clone_from_election_id') is-invalid @enderror"
                                        id="clone_from_election_id" name="clone_from_election_id">
                                        <option value="">— Start without cloning —</option>
                                        @foreach($sourceElections as $source)
                                            <option value="{{ $source->id }}" @selected(old('clone_from_election_id') == $source->id)>
                                                {{ $source->election_year }} — {{ $source->title }} ({{ $source->offices->count() }} offices)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('clone_from_election_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="election_year" class="form-label">Election Year</label>
                                    <input type="number" class="form-control @error('election_year') is-invalid @enderror"
                                        id="election_year" name="election_year"
                                        value="{{ old('election_year', date('Y') + 1) }}" required>
                                    @error('election_year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="cycle_label" class="form-label">Cycle Label (optional)</label>
                                    <input type="text" class="form-control" id="cycle_label" name="cycle_label"
                                        value="{{ old('cycle_label') }}" placeholder="e.g. 2026 Alumni Elections">
                                </div>

                                <div class="mb-3">
                                    <label for="title" class="form-label">Election Title</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                        id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="eligibility_criteria" class="form-label">Eligibility Criteria</label>
                                    <textarea class="form-control @error('eligibility_criteria') is-invalid @enderror"
                                        id="eligibility_criteria" name="eligibility_criteria" rows="3" required>{{ old('eligibility_criteria') }}</textarea>
                                    @error('eligibility_criteria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header"><h6 class="mb-0">Expression of Interest</h6></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="eoi_start" class="form-label">EOI Start</label>
                                            <input type="datetime-local" class="form-control" id="eoi_start" name="eoi_start"
                                                value="{{ old('eoi_start') }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="eoi_end" class="form-label">EOI End</label>
                                            <input type="datetime-local" class="form-control" id="eoi_end" name="eoi_end"
                                                value="{{ old('eoi_end') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header"><h6 class="mb-0">Accreditation</h6></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="accreditation_start" class="form-label">Start</label>
                                            <input type="datetime-local" class="form-control" id="accreditation_start"
                                                name="accreditation_start" value="{{ old('accreditation_start') }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="accreditation_end" class="form-label">End</label>
                                            <input type="datetime-local" class="form-control" id="accreditation_end"
                                                name="accreditation_end" value="{{ old('accreditation_end') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header"><h6 class="mb-0">Voting (same day)</h6></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="voting_start" class="form-label">Start</label>
                                            <input type="datetime-local" class="form-control" id="voting_start"
                                                name="voting_start" value="{{ old('voting_start') }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="voting_end" class="form-label">End</label>
                                            <input type="datetime-local" class="form-control" id="voting_end"
                                                name="voting_end" value="{{ old('voting_end') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('elcom.elections.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Create New Cycle</button>
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
document.getElementById('clone_from_election_id')?.addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    if (!option.value) return;
    const titleField = document.getElementById('title');
    const yearField = document.getElementById('election_year');
    if (!titleField.value) {
        const year = option.textContent.split('—')[0].trim();
        titleField.value = year + ' Alumni Elections';
    }
    if (yearField && option.value) {
        const match = option.textContent.match(/^(\d{4})/);
        if (match) yearField.value = parseInt(match[1], 10) + 1;
    }
});
</script>
@endpush
