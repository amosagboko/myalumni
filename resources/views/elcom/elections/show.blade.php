@extends('layouts.elcom')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Election Details</h4>
                    <div>
                        <a href="{{ route('elcom.elections.index') }}" class="btn btn-secondary btn-sm me-2">
                            <i class="fas fa-arrow-left"></i> Back to Elections
                        </a>
                        @if($election->status === 'draft')
                            <a href="{{ route('elcom.elections.edit', $election) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit Election
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5 class="card-title">{{ $election->title }}</h5>
                            <p class="text-muted">{{ $election->description }}</p>
                            
                            <div class="mb-3">
                                <h6>Eligibility Criteria</h6>
                                <p>{{ $election->eligibility_criteria }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2">Status</h6>
                                    <span class="badge bg-{{ $election->status === 'draft' ? 'secondary' : 
                                        ($election->status === 'accreditation' ? 'info' : 
                                        ($election->status === 'voting' ? 'primary' : 
                                        ($election->status === 'completed' ? 'success' : 'danger'))) }}">
                                        {{ ucfirst($election->status) }}
                                    </span>

                                    <h6 class="card-subtitle mb-2 mt-3">Statistics</h6>
                                    <ul class="list-unstyled">
                                        <li>Total Offices: {{ $election->offices->count() }}</li>
                                        <li>Total EOI: {{ $election->getPaidEoiApplicationsCount() }}</li>
                                        <li>Total Accredited: {{ $election->getTotalAccreditedVoters() }}</li>
                                    </ul>

                                    @if($election->status !== 'draft')
                                        <a href="{{ route('elcom.elections.accredited-voters', $election) }}" class="btn btn-info btn-sm w-100 mt-2">
                                            <i class="bi bi-people me-2"></i>View Accredited Voters
                                        </a>
                                        @if(in_array($election->status, ['voting', 'completed']))
                                            <a href="{{ route('elcom.elections.basic-results', $election) }}" class="btn btn-primary btn-sm w-100 mt-2">
                                                <i class="bi bi-bar-chart me-2"></i>View Basic Results
                                            </a>
                                        @endif
                                    @endif

                                    @if($election->status === 'draft' && $election->hasAccreditationStarted() && !$election->hasAccreditationEnded())
                                        <form action="{{ route('elcom.elections.start-accreditation', $election) }}" method="POST" class="mt-3">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm w-100" 
                                                onclick="return confirm('Are you sure you want to start the accreditation period?')">
                                                Start Accreditation
                                            </button>
                                        </form>
                                    @elseif($election->status === 'accreditation' && $election->canStartVoting())
                                        <form action="{{ route('elcom.elections.start-voting', $election) }}" method="POST" class="mt-3">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm w-100"
                                                onclick="return confirm('Are you sure you want to start the voting period?')">
                                                Start Voting
                                            </button>
                                        </form>
                                    @elseif($election->status === 'voting' && $election->canEndVoting())
                                        <form action="{{ route('elcom.elections.end-voting', $election) }}" method="POST" class="mt-3">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm w-100"
                                                onclick="return confirm('Are you sure you want to end the voting period? This will declare the results.')">
                                                End Voting
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- EOI Extension Alert -->
                    @if($election->canExtendEoiPeriod())
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">EOI Period Extension Available</h6>
                                    <p class="mb-2">
                                        @php
                                            $extensionReasons = $election->getEoiExtensionReasons();
                                            $officesWithNoCandidates = $election->getOfficesWithNoCandidatesCount();
                                            $totalOffices = $election->offices->count();
                                        @endphp
                                        
                                        @if($election->isEoiPeriodActive())
                                            @php
                                                $daysUntilEnd = floor(now()->diffInDays($election->eoi_end, false));
                                                $hoursUntilEnd = now()->diffInHours($election->eoi_end, false);
                                                $remainingHours = $hoursUntilEnd % 24;
                                                
                                                if ($daysUntilEnd >= 1) {
                                                    if ($remainingHours > 0) {
                                                        $timeRemaining = $daysUntilEnd . ' day' . ($daysUntilEnd > 1 ? 's' : '') . ' and ' . $remainingHours . ' hour' . ($remainingHours > 1 ? 's' : '');
                                                    } else {
                                                        $timeRemaining = $daysUntilEnd . ' day' . ($daysUntilEnd > 1 ? 's' : '');
                                                    }
                                                } else {
                                                    $timeRemaining = $hoursUntilEnd . ' hour' . ($hoursUntilEnd > 1 ? 's' : '');
                                                }
                                            @endphp
                                            <strong>Grace Period:</strong> EOI period ends in <strong>{{ $timeRemaining }}</strong>.
                                            @if($officesWithNoCandidates > 0)
                                                <br><strong>Note:</strong> {{ $officesWithNoCandidates }} out of {{ $totalOffices }} office(s) have no candidates yet.
                                            @endif
                                        @else
                                            <strong>Extension Available:</strong> EOI period has ended but can be extended.
                                        @endif
                                    </p>
                                    <a href="{{ route('elcom.elections.eoi-payment-status', $election) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-clock me-1"></i>
                                        Extend EOI Period
                                    </a>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <hr>

                    <!-- Expression of Interest Period Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Expression of Interest Period</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="timeline-item">
                                        <div class="timeline-marker {{ $election->hasEoiStarted() ? 'bg-success' : 'bg-secondary' }}"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">EOI Timeline</h6>
                                            <p class="text-muted mb-2">
                                                @if($election->eoi_start && $election->eoi_end)
                                                    {{ $election->eoi_start->format('M d, Y h:i A') }} - 
                                                    {{ $election->eoi_end->format('M d, Y h:i A') }}
                                                @else
                                                    Not set
                                                @endif
                                            </p>
                                            @if($election->isEoiPeriodActive())
                                                <span class="badge bg-success">Active</span>
                                            @elseif($election->hasEoiEnded())
                                                <span class="badge bg-secondary">Ended</span>
                                            @else
                                                <span class="badge bg-warning">Not Started</span>
                                            @endif

                                            @if(auth()->user()->hasRole(['administrator', 'elcom-chair']))
                                                <div class="mt-2">
                                                    <!-- EOI Debug Info -->
                                                    <div class="alert alert-warning small">
                                                        <strong>EOI Debug Info:</strong><br>
                                                        Status: {{ $election->status }}<br>
                                                        hasEoiStarted(): {{ $election->hasEoiStarted() ? 'true' : 'false' }}<br>
                                                        hasEoiEnded(): {{ $election->hasEoiEnded() ? 'true' : 'false' }}<br>
                                                        isEoiPeriodActive(): {{ $election->isEoiPeriodActive() ? 'true' : 'false' }}<br>
                                                        canStartEoi(): {{ $election->canStartEoi() ? 'true' : 'false' }}<br>
                                                        canEndEoi(): {{ $election->canEndEoi() ? 'true' : 'false' }}<br>
                                                        EOI Start: {{ $election->eoi_start ? $election->eoi_start->format('Y-m-d H:i:s') : 'Not set' }}<br>
                                                        EOI End: {{ $election->eoi_end ? $election->eoi_end->format('Y-m-d H:i:s') : 'Not set' }}
                                                    </div>
                                                    
                                                    @if(!$election->hasEoiStarted() && $election->canStartEoi())
                                                        <form action="{{ route('elcom.elections.start-eoi', $election) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                Start EOI Period
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($election->isEoiPeriodActive() && $election->canEndEoi())
                                                        <form action="{{ route('elcom.elections.end-eoi', $election) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-warning">
                                                                End EOI Period
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($election->canExtendEoiPeriod())
                                                        <a href="{{ route('elcom.elections.eoi-payment-status', $election) }}" class="btn btn-sm btn-info">
                                                            <i class="bi bi-clock me-1"></i>
                                                            Extend EOI Period
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-2">EOI Statistics</h6>
                                            <ul class="list-unstyled">
                                                <li>Total Expressions: {{ $election->getPaidEoiApplicationsCount() }}</li>
                                                <li>Pending Review: {{ $election->candidates()->where('has_paid_screening_fee', true)->where('status', 'pending')->count() }}</li>
                                                <li>Approved: {{ $election->candidates()->where('has_paid_screening_fee', true)->where('status', 'approved')->count() }}</li>
                                                <li>Rejected: {{ $election->candidates()->where('has_paid_screening_fee', true)->where('status', 'rejected')->count() }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accreditation Period Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Accreditation Period</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline-item">
                                <div class="timeline-marker {{ $election->hasAccreditationStarted() ? 'bg-success' : 'bg-secondary' }}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Accreditation Timeline</h6>
                                    <p class="text-muted mb-2">
                                        @if($election->accreditation_start && $election->accreditation_end)
                                            {{ $election->accreditation_start->format('M d, Y h:i A') }} - 
                                            {{ $election->accreditation_end->format('M d, Y h:i A') }}
                                        @else
                                            Not set
                                        @endif
                                    </p>
                                    @if($election->isAccreditationPeriodActive())
                                        <span class="badge bg-success">Active</span>
                                    @elseif($election->hasAccreditationEnded())
                                        <span class="badge bg-secondary">Ended</span>
                                    @else
                                        <span class="badge bg-warning">Not Started</span>
                                    @endif

                                    @if(auth()->user()->hasRole(['administrator', 'elcom-chair']))
                                        <div class="mt-2">
                                            <!-- Debug Info -->
                                            <div class="alert alert-info small">
                                                <strong>Debug Info:</strong><br>
                                                Status: {{ $election->status }}<br>
                                                hasAccreditationStarted(): {{ $election->hasAccreditationStarted() ? 'true' : 'false' }}<br>
                                                hasAccreditationEnded(): {{ $election->hasAccreditationEnded() ? 'true' : 'false' }}<br>
                                                isAccreditationPeriodActive(): {{ $election->isAccreditationPeriodActive() ? 'true' : 'false' }}<br>
                                                canStartAccreditation(): {{ $election->canStartAccreditation() ? 'true' : 'false' }}<br>
                                                User Roles: {{ auth()->user()->roles->pluck('name')->implode(', ') }}<br>
                                                Current Time: {{ now()->format('Y-m-d H:i:s') }}<br>
                                                Accreditation Start: {{ $election->accreditation_start->format('Y-m-d H:i:s') }}<br>
                                                Accreditation End: {{ $election->accreditation_end->format('Y-m-d H:i:s') }}
                                            </div>
                                            
                                            @if($election->status === 'draft' && $election->hasAccreditationStarted() && !$election->hasAccreditationEnded())
                                                <form action="{{ route('elcom.elections.start-accreditation', $election) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        Start Accreditation
                                                    </button>
                                                </form>
                                            @endif

                                            @if($election->status === 'accreditation' && $election->isAccreditationPeriodActive() && $election->canEndAccreditation())
                                                <form action="{{ route('elcom.elections.end-accreditation', $election) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        End Accreditation
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Voting Period Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Voting Period</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline-item">
                                <div class="timeline-marker {{ $election->hasVotingStarted() ? 'bg-success' : 'bg-secondary' }}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Voting Timeline</h6>
                                    <p class="text-muted mb-2">
                                        @if($election->voting_start && $election->voting_end)
                                            {{ $election->voting_start->format('M d, Y h:i A') }} - 
                                            {{ $election->voting_end->format('M d, Y h:i A') }}
                                            <small class="d-block text-info mt-1">
                                                Note: Voting must be completed on the same day
                                            </small>
                                        @else
                                            Not set
                                        @endif
                                    </p>
                                    @if($election->isVotingPeriodActive())
                                        <span class="badge bg-success">Active</span>
                                    @elseif($election->hasVotingEnded())
                                        <span class="badge bg-secondary">Ended</span>
                                    @else
                                        <span class="badge bg-warning">Not Started</span>
                                    @endif

                                    @if(auth()->user()->hasRole(['administrator', 'elcom-chair']))
                                        <div class="mt-2">
                                            @if(!$election->hasVotingStarted() && $election->canStartVoting())
                                                <form action="{{ route('elcom.elections.start-voting', $election) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                        onclick="return confirm('Are you sure you want to start the voting period? This will allow voters to cast their votes.')">
                                                        Start Voting
                                                    </button>
                                                </form>
                                            @endif

                                            @if($election->isVotingPeriodActive() && $election->canEndVoting())
                                                <form action="{{ route('elcom.elections.end-voting', $election) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning"
                                                        onclick="return confirm('Are you sure you want to end the voting period? This will declare the results.')">
                                                        End Voting
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3">Election Offices</h5>
                    <div class="row">
                        @forelse($election->offices as $office)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ $office->title }}</h6>
                                        @if($election->status === 'draft')
                                            <a href="{{ route('elcom.election-offices.edit', [$election, $office]) }}" 
                                                class="btn btn-sm btn-outline-primary">
                                                Edit Office
                                            </a>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">{{ $office->description }}</p>
                                        <ul class="list-unstyled">
                                            <li><strong>Max Candidates:</strong> {{ $office->max_candidates }}</li>
                                            <li><strong>Term Duration:</strong> {{ $office->term_duration }} years</li>
                                            <li><strong>Total Candidates:</strong> {{ $office->candidates->count() }}</li>
                                        </ul>
                                        <a href="{{ route('elcom.election-offices.candidates.index', [$election, $office]) }}" 
                                            class="btn btn-info btn-sm">
                                            Manage Candidates
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    No offices have been created for this election.
                                    @if($election->status === 'draft')
                                        <a href="{{ route('elcom.election-offices.create', $election) }}" class="alert-link">
                                            Create an office
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if($election->status === 'draft')
                        <div class="mt-3">
                            <a href="{{ route('elcom.election-offices.create', $election) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Add New Office
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 