@extends('layouts.alumni')

@section('content')
@php
    $hubService = app(\App\Services\Alumni\AlumniElectionHubService::class);
@endphp

<div class="elections-hub-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-600 mb-1">Elections</h4>
                <p class="text-grey-500 font-xssss mb-0">
                    Follow the active election cycle, track your participation, and view past results.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($actions['express_interest'] ?? false)
                    <a href="{{ route('alumni.elections.expression-of-interest.status') }}" class="btn btn-outline-success btn-sm">
                        <i class="feather-clipboard me-1"></i> EOI Status
                    </a>
                @endif
                <a href="{{ route('alumni.home') }}" class="btn btn-outline-secondary btn-sm">
                    Dashboard
                </a>
            </div>
        </div>

        <div class="card-body p-4 w-100 border-0">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-600 mb-0 font-xssss text-grey-900 text-uppercase">Current Election</h5>
                @if($currentElection && $phaseLabel)
                    <span class="badge elections-hub-phase-pill {{ $phaseBadgeClass }}">{{ $phaseLabel }}</span>
                @endif
            </div>

            @if(!$currentElection)
                <div class="text-center py-5 border rounded-3 bg-light">
                    <i class="feather-calendar font-xl text-grey-500 d-block mb-3"></i>
                    <p class="text-grey-700 font-xssss mb-1">There is no active election cycle at the moment.</p>
                    <p class="text-grey-500 font-xssss mb-0">Check past elections below for historical results.</p>
                </div>
            @else
                @if($currentElection->isByElection() && $parentElection)
                    <div class="alert alert-info font-xssss py-2 mb-3">
                        <i class="feather-info me-1"></i>
                        This is a <strong>by-election</strong> for
                        <a href="{{ route('alumni.elections.results', $parentElection) }}">{{ $parentElection->title }}</a>.
                    </div>
                @elseif($currentElection->isIncomplete())
                    <div class="alert alert-warning font-xssss py-2 mb-3">
                        <i class="feather-alert-triangle me-1"></i>
                        Main election is <strong>incomplete</strong> — some offices await a by-election.
                        @if($actions['view_results'] ?? false)
                            <a href="{{ route('alumni.elections.results', $currentElection) }}">View partial results</a>
                        @endif
                    </div>
                @endif

                <div class="mb-4">
                    <h5 class="fw-600 mb-1">{{ $currentElection->title }}</h5>
                    @if($currentElection->cycle_label)
                        <p class="text-grey-500 font-xssss mb-2">{{ $currentElection->cycle_label }}</p>
                    @endif
                    @if($currentElection->description)
                        <p class="text-grey-500 font-xssss mb-0">{{ Str::limit($currentElection->description, 220) }}</p>
                    @endif
                </div>

                @if($participation)
                    <h6 class="fw-600 font-xssss text-grey-900 text-uppercase mb-3">Your participation</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="elections-hub-stat">
                                <div class="elections-hub-stat__label">Expression of Interest</div>
                                <div class="elections-hub-stat__value">
                                    @if($participation['eoi'])
                                        <span class="badge {{ $hubService->eoiStatusBadgeClass($participation['eoi']['status']) }}">
                                            {{ $participation['eoi']['status_label'] ?? ucfirst(str_replace('_', ' ', $participation['eoi']['status'])) }}
                                        </span>
                                        @if($participation['eoi']['office'])
                                            <div class="text-grey-500 font-xssss mt-2">{{ $participation['eoi']['office'] }}</div>
                                        @endif
                                    @else
                                        <span class="text-grey-500">Not submitted</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="elections-hub-stat">
                                <div class="elections-hub-stat__label">Accreditation</div>
                                <div class="elections-hub-stat__value">
                                    @if($participation['is_accredited'])
                                        <span class="badge bg-success">Accredited</span>
                                        @if($participation['accredited_at'])
                                            <div class="text-grey-500 font-xssss mt-2">{{ $participation['accredited_at']->format('M d, Y') }}</div>
                                        @endif
                                    @else
                                        <span class="text-grey-500">Not accredited</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="elections-hub-stat">
                                <div class="elections-hub-stat__label">Vote</div>
                                <div class="elections-hub-stat__value">
                                    @if($participation['has_voted'])
                                        <span class="badge bg-success">Ballot cast</span>
                                        @if($participation['voted_at'])
                                            <div class="text-grey-500 font-xssss mt-2">{{ $participation['voted_at']->format('M d, Y h:i A') }}</div>
                                        @endif
                                    @elseif($participation['is_accredited'])
                                        <span class="text-grey-500">Not yet voted</span>
                                    @else
                                        <span class="text-grey-500">Awaiting accreditation</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($currentElection->offices->isNotEmpty())
                    <h6 class="fw-600 font-xssss text-grey-900 text-uppercase mb-3">Offices</h6>
                    <div class="d-flex flex-column gap-2 mb-4">
                        @foreach($currentElection->offices as $office)
                            <div class="elections-hub-office">
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-lg-6">
                                        <div class="fw-600 font-xssss text-grey-900">{{ $office->title }}</div>
                                        @if($office->description)
                                            <div class="text-grey-500 font-xssss">{{ Str::limit($office->description, 100) }}</div>
                                        @endif
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                            @if($office->isRunoffByElectionOffice())
                                                <span class="badge bg-info text-dark">
                                                    <i class="feather-repeat me-1"></i> Runoff — candidates on ballot
                                                </span>
                                            @elseif($actions['express_interest'] ?? false)
                                                @if($office->isAcceptingApplications())
                                                    <a href="{{ route('alumni.elections.expression-of-interest.form', [$currentElection, $office]) }}"
                                                       class="btn btn-sm btn-success">
                                                        <i class="feather-edit-3 me-1"></i> Express Interest
                                                    </a>
                                                    @if($office->getRemainingApplicantSlots() <= 3)
                                                        <span class="text-grey-500 font-xssss align-self-center">
                                                            {{ $office->getRemainingApplicantSlots() }} slot{{ $office->getRemainingApplicantSlots() === 1 ? '' : 's' }} left
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="feather-lock me-1"></i> EOI closed — slots full
                                                    </span>
                                                @endif
                                            @endif
                                            @if($actions['view_candidates'] ?? false)
                                                <a href="{{ route('alumni.elections.published-candidates', [$currentElection, $office]) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="feather-users me-1"></i> View Candidates
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                    @if($actions['express_interest'] ?? false)
                        <a href="{{ route('alumni.elections.expression-of-interest.status') }}" class="btn btn-outline-success btn-sm">
                            <i class="feather-clipboard me-1"></i> My EOI Status
                        </a>
                    @endif
                    @if($actions['accredit'] ?? false)
                        <a href="{{ route('alumni.elections.accreditation', $currentElection) }}" class="btn btn-info btn-sm text-white">
                            <i class="feather-user-check me-1"></i> Get Accredited
                        </a>
                    @endif
                    @if(($actions['view_accreditation_status'] ?? false) && !($actions['accredit'] ?? false))
                        <a href="{{ route('alumni.elections.accreditation', $currentElection) }}" class="btn btn-outline-info btn-sm">
                            <i class="feather-user-check me-1"></i> Accreditation Status
                        </a>
                    @endif
                    @if($actions['vote'] ?? false)
                        <a href="{{ route('alumni.elections.vote', $currentElection) }}" class="btn btn-primary btn-sm">
                            <i class="feather-check-square me-1"></i> Cast Your Vote
                        </a>
                    @endif
                    @if(($actions['view_vote_page'] ?? false) && !($actions['vote'] ?? false))
                        <a href="{{ route('alumni.elections.vote', $currentElection) }}" class="btn btn-outline-primary btn-sm">
                            <i class="feather-check-square me-1"></i> Voting Page
                        </a>
                    @endif
                    @if($actions['live_results'] ?? false)
                        <a href="{{ route('alumni.elections.results', $currentElection) }}" class="btn btn-secondary btn-sm">
                            <i class="feather-bar-chart-2 me-1"></i> Live Results
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-600 mb-0">Past Elections</h5>
            @if($pastElections->total() === 1)
                @php $solePastElection = $pastElections->first(); @endphp
                <span class="badge {{ $solePastElection->isArchived() ? 'bg-secondary' : 'bg-success' }}">
                    {{ $solePastElection->isArchived() ? 'Archived' : 'Completed' }}
                </span>
            @endif
        </div>
        <div class="card-body p-4 w-100 border-0">
            @if($pastElections->isEmpty())
                <p class="text-grey-500 font-xssss mb-0 text-center py-4">No past election records yet.</p>
            @else
                @foreach($pastElections as $election)
                    <div class="elections-hub-past-item">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="min-w-0">
                                <div class="fw-600 font-xssss text-grey-900 text-break">{{ $election->title }}</div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                    @if($election->election_year)
                                        <span class="text-grey-500 font-xssss">{{ $election->election_year }}</span>
                                    @endif
                                    @if($election->isArchived() && $election->archived_at)
                                        <span class="text-grey-500 font-xssss">Archived {{ $election->archived_at->format('M d, Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('alumni.elections.results', $election) }}" class="btn btn-sm btn-outline-primary text-nowrap">
                                <i class="feather-award me-1"></i> View Results
                            </a>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-center mt-4">
                    {{ $pastElections->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumni-elections-hub.css') }}">
@endpush
@endsection
