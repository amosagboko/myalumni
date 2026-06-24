@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">

            {{-- Current election --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <h3 class="card-title mb-0 h4 h-md-3">Current Election</h3>
                    @if($currentElection && $phaseLabel)
                        <span class="badge bg-primary fs-6">{{ $phaseLabel }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if(!$currentElection)
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">There is no active election cycle at the moment.</p>
                            <p class="text-muted small mt-2 mb-0">Check past elections below for historical results.</p>
                        </div>
                    @else
                        <div class="mb-3">
                            <h5 class="fw-bold mb-1">{{ $currentElection->title }}</h5>
                            @if($currentElection->cycle_label)
                                <p class="text-muted small mb-2">{{ $currentElection->cycle_label }}</p>
                            @endif
                            @if($currentElection->description)
                                <p class="text-muted small mb-0">{{ Str::limit($currentElection->description, 200) }}</p>
                            @endif
                        </div>

                        @if($participation)
                            <div class="card border-light bg-light mb-4">
                                <div class="card-body py-3">
                                    <h6 class="text-muted small text-uppercase mb-3">Your participation</h6>
                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi bi-pencil-square text-success mt-1"></i>
                                                <div>
                                                    <span class="fw-medium small d-block">Expression of Interest</span>
                                                    @if($participation['eoi'])
                                                        <span class="badge bg-{{ $participation['eoi']['status'] === 'approved' ? 'success' : ($participation['eoi']['status'] === 'rejected' ? 'danger' : 'warning') }}">
                                                            {{ ucfirst($participation['eoi']['status']) }}
                                                        </span>
                                                        @if($participation['eoi']['office'])
                                                            <small class="text-muted d-block mt-1">{{ $participation['eoi']['office'] }}</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted small">Not submitted</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi bi-person-check text-info mt-1"></i>
                                                <div>
                                                    <span class="fw-medium small d-block">Accreditation</span>
                                                    @if($participation['is_accredited'])
                                                        <span class="badge bg-success">Accredited</span>
                                                        @if($participation['accredited_at'])
                                                            <small class="text-muted d-block mt-1">{{ $participation['accredited_at']->format('M d, Y') }}</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted small">Not accredited</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi bi-check2-square text-primary mt-1"></i>
                                                <div>
                                                    <span class="fw-medium small d-block">Vote</span>
                                                    @if($participation['has_voted'])
                                                        <span class="badge bg-success">Ballot cast</span>
                                                        @if($participation['voted_at'])
                                                            <small class="text-muted d-block mt-1">{{ $participation['voted_at']->format('M d, Y h:i A') }}</small>
                                                        @endif
                                                    @elseif($participation['is_accredited'])
                                                        <span class="text-muted small">Not yet voted</span>
                                                    @else
                                                        <span class="text-muted small">Awaiting accreditation</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($currentElection->offices->isNotEmpty())
                            <div class="mb-4">
                                <h6 class="text-muted mb-2 small">Offices</h6>
                                @foreach($currentElection->offices as $office)
                                    <div class="card mb-2 border-light">
                                        <div class="card-body py-2">
                                            <div class="row align-items-center">
                                                <div class="col-12 col-md-6 mb-2 mb-md-0">
                                                    <span class="fw-medium small">{{ $office->title }}</span>
                                                    @if($office->description)
                                                        <small class="text-muted d-block">{{ Str::limit($office->description, 80) }}</small>
                                                    @endif
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @if($actions['express_interest'] ?? false)
                                                            <a href="{{ route('alumni.elections.expression-of-interest.form', [$currentElection, $office]) }}"
                                                               class="btn btn-sm btn-success w-100 w-md-auto">
                                                                <i class="bi bi-pencil-square me-1"></i>
                                                                <span class="d-none d-sm-inline">Express Interest</span>
                                                                <span class="d-inline d-sm-none">EOI</span>
                                                            </a>
                                                        @endif
                                                        @if($actions['view_candidates'] ?? false)
                                                            <a href="{{ route('alumni.elections.published-candidates', [$currentElection, $office]) }}"
                                                               class="btn btn-sm btn-outline-primary w-100 w-md-auto">
                                                                <i class="bi bi-people me-1"></i>
                                                                <span class="d-none d-sm-inline">View Candidates</span>
                                                                <span class="d-inline d-sm-none">Candidates</span>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="d-flex flex-column flex-md-row flex-wrap gap-2">
                            @if($actions['express_interest'] ?? false)
                                <a href="{{ route('alumni.elections.expression-of-interest.status') }}"
                                   class="btn btn-outline-success w-100 w-md-auto">
                                    <i class="bi bi-clipboard-check me-2"></i>
                                    My EOI Status
                                </a>
                            @endif
                            @if($actions['accredit'] ?? false)
                                <a href="{{ route('alumni.elections.accreditation', $currentElection) }}"
                                   class="btn btn-info w-100 w-md-auto">
                                    <i class="bi bi-person-check me-2"></i>
                                    Get Accredited
                                </a>
                            @endif
                            @if(($actions['view_accreditation_status'] ?? false) && !($actions['accredit'] ?? false))
                                <a href="{{ route('alumni.elections.accreditation', $currentElection) }}"
                                   class="btn btn-outline-info w-100 w-md-auto">
                                    <i class="bi bi-person-check me-2"></i>
                                    Accreditation Status
                                </a>
                            @endif
                            @if($actions['vote'] ?? false)
                                <a href="{{ route('alumni.elections.vote', $currentElection) }}"
                                   class="btn btn-primary w-100 w-md-auto">
                                    <i class="bi bi-check2-square me-2"></i>
                                    Cast Your Vote
                                </a>
                            @endif
                            @if(($actions['view_vote_page'] ?? false) && !($actions['vote'] ?? false))
                                <a href="{{ route('alumni.elections.vote', $currentElection) }}"
                                   class="btn btn-outline-primary w-100 w-md-auto">
                                    <i class="bi bi-check2-square me-2"></i>
                                    Voting Page
                                </a>
                            @endif
                            @if($actions['live_results'] ?? false)
                                <a href="{{ route('alumni.elections.results', $currentElection) }}"
                                   class="btn btn-secondary w-100 w-md-auto">
                                    <i class="bi bi-bar-chart me-2"></i>
                                    Live Results
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Past elections --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
                    <h3 class="card-title mb-0 h4 h-md-3">Past Elections</h3>
                    @if($pastElections->count() === 1)
                        @php $solePastElection = $pastElections->first(); @endphp
                        <span class="badge fs-6 flex-shrink-0 {{ $solePastElection->isArchived() ? 'bg-secondary' : 'bg-success' }}">
                            {{ $solePastElection->isArchived() ? 'Archived' : 'Completed' }}
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    @if($pastElections->isEmpty())
                        <p class="text-muted mb-0 text-center py-3">No past election records yet.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($pastElections as $election)
                                <div class="list-group-item px-0 py-3 border-bottom">
                                    <div class="row align-items-center g-2 g-md-3 past-election-row">
                                        <div class="col past-election-title">
                                            <h6 class="fw-bold mb-0 text-break">{{ $election->title }}</h6>
                                            @if($election->election_year || ($election->isArchived() && $election->archived_at))
                                                <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                    @if($election->election_year)
                                                        <span class="text-muted small">{{ $election->election_year }}</span>
                                                    @endif
                                                    @if($election->isArchived() && $election->archived_at)
                                                        <small class="text-muted">Archived {{ $election->archived_at->format('M d, Y') }}</small>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-auto ms-md-auto">
                                            <a href="{{ route('alumni.elections.results', $election) }}"
                                               class="btn btn-sm btn-outline-success text-nowrap">
                                                <i class="bi bi-trophy me-1"></i>
                                                View Results
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@media (max-width: 767.98px) {
    .card-body {
        padding: 1rem;
    }

    .h-md-3 {
        font-size: 1.5rem !important;
    }

    .btn {
        font-size: 0.875rem;
        padding: 0.75rem 1rem;
    }

    .btn-sm {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }

    .badge {
        font-size: 0.75rem !important;
    }
}

.past-election-row {
    flex-wrap: nowrap;
}

.past-election-title {
    min-width: 0;
    flex: 1 1 auto;
}

@media (max-width: 575.98px) {
    .past-election-row {
        flex-wrap: wrap;
    }

    .past-election-row .col-auto {
        padding-top: 0.25rem;
    }
}

@media (min-width: 768px) {
    .w-md-auto {
        width: auto !important;
    }
}

@media (min-width: 992px) {
    .w-lg-auto {
        width: auto !important;
    }
}
</style>
@endsection
