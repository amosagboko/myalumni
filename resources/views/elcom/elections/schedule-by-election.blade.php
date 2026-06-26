@extends('layouts.elcom')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-0">Schedule By-Election</h4>
                        <small class="text-muted">For: {{ $election->title }}</small>
                    </div>
                    <a href="{{ route('elcom.elections.resolution', $election) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Resolution
                    </a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="alert alert-info">
                        <ul class="mb-0 small">
                            <li><strong>Tied offices</strong> — runoff only; tied candidates are placed on the ballot automatically (no EOI).</li>
                            <li><strong>Uncontested offices</strong> — EOI, screening, accreditation, and voting apply.</li>
                            <li>Both types can be included in one by-election event.</li>
                        </ul>
                    </div>

                    <form action="{{ route('elcom.elections.schedule-by-election.store', $election) }}" method="POST" id="by-election-form">
                        @csrf

                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Select offices</h6>
                            @error('office_ids')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror
                            @foreach($schedulableOffices as $office)
                                <div class="form-check border rounded p-3 mb-2">
                                    <input class="form-check-input office-checkbox" type="checkbox"
                                        name="office_ids[]" value="{{ $office->id }}" id="office_{{ $office->id }}"
                                        data-mode="{{ $office->isTied() ? 'runoff' : 'eoi' }}"
                                        @checked(is_array(old('office_ids')) && in_array($office->id, old('office_ids')))>
                                    <label class="form-check-label w-100" for="office_{{ $office->id }}">
                                        <span class="fw-semibold">{{ $office->title }}</span>
                                        @if($office->isTied())
                                            <span class="badge bg-danger ms-2">Tie — runoff</span>
                                        @else
                                            <span class="badge bg-secondary ms-2">Uncontested — EOI</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">By-Election Title</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                        id="title" name="title"
                                        value="{{ old('title', ($election->election_year ?? date('Y')) . ' By-Election') }}" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="cycle_label" class="form-label">Label (optional)</label>
                                    <input type="text" class="form-control" id="cycle_label" name="cycle_label"
                                        value="{{ old('cycle_label') }}" placeholder="e.g. Runoff & Vacant Seats">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description (optional)</label>
                                    <textarea class="form-control" id="description" name="description" rows="2">{{ old('description') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3" id="eoi-dates-card">
                                    <div class="card-header"><h6 class="mb-0">Expression of Interest <small class="text-muted">(uncontested offices only)</small></h6></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="eoi_start" class="form-label">EOI Start</label>
                                            <input type="datetime-local" class="form-control @error('eoi_start') is-invalid @enderror"
                                                id="eoi_start" name="eoi_start" value="{{ old('eoi_start') }}">
                                            @error('eoi_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="eoi_end" class="form-label">EOI End</label>
                                            <input type="datetime-local" class="form-control @error('eoi_end') is-invalid @enderror"
                                                id="eoi_end" name="eoi_end" value="{{ old('eoi_end') }}">
                                            @error('eoi_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            <a href="{{ route('elcom.elections.resolution', $election) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning">Schedule By-Election</button>
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
(function () {
    const checkboxes = document.querySelectorAll('.office-checkbox');
    const eoiStart = document.getElementById('eoi_start');
    const eoiEnd = document.getElementById('eoi_end');
    const eoiCard = document.getElementById('eoi-dates-card');

    function toggleEoiRequired() {
        const needsEoi = Array.from(checkboxes).some(cb => cb.checked && cb.dataset.mode === 'eoi');
        eoiStart.required = needsEoi;
        eoiEnd.required = needsEoi;
        eoiCard.classList.toggle('border-warning', needsEoi);
    }

    checkboxes.forEach(cb => cb.addEventListener('change', toggleEoiRequired));
    toggleEoiRequired();
})();
</script>
@endpush
