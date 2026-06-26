@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title h4 h-md-3">
                        <i class="bi bi-trophy me-2 text-warning"></i>
                        Election Results - {{ $election->title }}
                    </h3>
                    <p class="text-muted mb-0 small">Final results declared on {{ $election->results->first()?->declared_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                </div>
                <div class="card-body">
                    @if(session('info'))
                        <div class="alert alert-info">{{ session('info') }}</div>
                    @endif

                    @if($election->isIncomplete())
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Election incomplete.</strong>
                            {{ $resolution['pending_count'] }} office(s) are tied or uncontested and will be resolved in a by-election.
                            Winners shown below are only for offices with a clear result.
                        </div>
                    @endif
                        <div class="alert alert-secondary mb-4">
                            <i class="bi bi-archive me-2"></i>
                            <strong>Archived election</strong>
                            @if($election->archived_at)
                                — archived on {{ $election->archived_at->format('M d, Y') }}.
                            @endif
                            Results are read-only historical records.
                        </div>
                    @endif

                    <!-- Election Statistics -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-2 p-md-3 text-center">
                                <h6 class="text-muted mb-1 mb-md-2 small">Total Accredited Voters</h6>
                                <h3 class="mb-0 text-primary h4 h-md-3">{{ number_format($totalAccredited) }}</h3>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-2 p-md-3 text-center">
                                <h6 class="text-muted mb-1 mb-md-2 small">Total Votes Cast</h6>
                                <h3 class="mb-0 text-success h4 h-md-3">{{ number_format($totalVotes) }}</h3>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-2 p-md-3 text-center">
                                <h6 class="text-muted mb-1 mb-md-2 small">Voter Turnout</h6>
                                <h3 class="mb-0 text-info h4 h-md-3">{{ $voterTurnout }}%</h3>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-2 p-md-3 text-center">
                                <h6 class="text-muted mb-1 mb-md-2 small">Offices Contested</h6>
                                <h3 class="mb-0 text-warning h4 h-md-3">{{ $election->offices->count() }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Winners Section -->
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0 h6 h-md-5">
                                <i class="bi bi-trophy-fill me-2"></i>
                                Election Winners
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($officeResults as $officeResult)
                                    @php
                                        $winner = $officeResult['candidates']->where('is_winner', true)->first();
                                    @endphp
                                    @if($winner)
                                        <div class="col-12 col-md-6">
                                            <div class="winner-card p-3 border border-success rounded">
                                                <div class="d-flex align-items-center">
                                                    @if($winner['candidate']->passport)
                                                        <img src="{{ asset('storage/' . $winner['candidate']->passport) }}" 
                                                             alt="Winner" 
                                                             class="rounded-circle me-2 me-md-3"
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    @endif
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 text-success small">
                                                            <i class="bi bi-trophy-fill me-1 me-md-2"></i>
                                                            {{ $officeResult['office']->title }}
                                                        </h6>
                                                        <h5 class="mb-1 h6 h-md-5">{{ $winner['candidate']->alumni->user->name }}</h5>
                                                        <p class="text-muted mb-1 small">{{ $winner['candidate']->alumni->matriculation_number }}</p>
                                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                                            <span class="badge bg-success mb-1 mb-md-0">{{ number_format($winner['votes']) }} votes</span>
                                                            <span class="text-muted small">{{ $winner['percentage'] }}% of total votes</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if(isset($resolution) && $resolution['has_pending'])
                        <div class="card mb-4 border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title mb-0 h6 h-md-5">
                                    <i class="bi bi-hourglass-split me-2"></i>
                                    Pending By-Election
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($resolution['tied'] as $item)
                                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <h6 class="text-danger mb-2">{{ $item['office']->title }} — <span class="badge bg-danger">Tie</span></h6>
                                        <ul class="mb-0 small">
                                            @foreach($item['tied_candidates'] as $candidate)
                                                <li>{{ $candidate->alumni->user->name }} ({{ number_format($candidate->votes_count) }} votes)</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                                @foreach($resolution['uncontested'] as $item)
                                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <h6 class="text-secondary mb-1">{{ $item['office']->title }} — <span class="badge bg-secondary">Uncontested</span></h6>
                                        <p class="small text-muted mb-0">No candidate contested this office.</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Detailed Results by Office -->
                    <h5 class="mb-3 h6 h-md-5">
                        <i class="bi bi-list-ul me-2"></i>
                        Detailed Results by Office
                    </h5>
                    
                    @foreach($officeResults as $officeResult)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0 small">{{ $officeResult['office']->title }}</h6>
                                <small class="text-muted">Total votes cast: {{ number_format($officeResult['total_votes']) }}</small>
                            </div>
                            <div class="card-body p-0">
                                <!-- Mobile View - Cards -->
                                <div class="d-md-none">
                                    @foreach($officeResult['candidates'] as $index => $candidate)
                                        <div class="candidate-card p-3 border-bottom {{ $candidate['is_winner'] ? 'bg-success bg-opacity-10' : '' }}">
                                            <div class="d-flex align-items-center mb-2">
                                                @if($candidate['candidate']->passport)
                                                    <img src="{{ asset('storage/' . $candidate['candidate']->passport) }}" 
                                                         alt="Candidate" 
                                                         class="rounded-circle me-2"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @endif
                                                <div class="flex-grow-1">
                                                    <div class="fw-medium small">{{ $candidate['candidate']->alumni->user->name }}</div>
                                                    <small class="text-muted">{{ $candidate['candidate']->alumni->matriculation_number }}</small>
                                                </div>
                                                <span class="badge {{ $index === 0 ? 'bg-success' : 'bg-secondary' }} ms-2">
                                                    {{ $index + 1 }}
                                                </span>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-6">
                                                    <strong class="small">{{ number_format($candidate['votes']) }} votes</strong>
                                                </div>
                                                <div class="col-6 text-end">
                                                    @if($candidate['is_winner'])
                                                        <span class="badge bg-success small">
                                                            <i class="bi bi-trophy-fill me-1"></i>
                                                            Winner
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary small">Not Elected</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <div class="progress" style="height: 15px;">
                                                    <div class="progress-bar {{ $candidate['is_winner'] ? 'bg-success' : 'bg-primary' }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $candidate['percentage'] }}%"
                                                         aria-valuenow="{{ $candidate['percentage'] }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <small>{{ $candidate['percentage'] }}%</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Desktop View - Table -->
                                <div class="d-none d-md-block">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Candidate</th>
                                                    <th>Votes</th>
                                                    <th>Percentage</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($officeResult['candidates'] as $index => $candidate)
                                                    <tr class="{{ $candidate['is_winner'] ? 'table-success' : '' }}">
                                                        <td>
                                                            <span class="badge {{ $index === 0 ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ $index + 1 }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if($candidate['candidate']->passport)
                                                                    <img src="{{ asset('storage/' . $candidate['candidate']->passport) }}" 
                                                                         alt="Candidate" 
                                                                         class="rounded-circle me-2"
                                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                                @endif
                                                                <div>
                                                                    <div class="fw-medium">{{ $candidate['candidate']->alumni->user->name }}</div>
                                                                    <small class="text-muted">{{ $candidate['candidate']->alumni->matriculation_number }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <strong>{{ number_format($candidate['votes']) }}</strong>
                                                        </td>
                                                        <td>
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar {{ $candidate['is_winner'] ? 'bg-success' : 'bg-primary' }}" 
                                                                     role="progressbar" 
                                                                     style="width: {{ $candidate['percentage'] }}%"
                                                                     aria-valuenow="{{ $candidate['percentage'] }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                    {{ $candidate['percentage'] }}%
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($candidate['is_winner'])
                                                                <span class="badge bg-success">
                                                                    <i class="bi bi-trophy-fill me-1"></i>
                                                                    Winner
                                                                </span>
                                                            @else
                                                                <span class="badge bg-secondary">Not Elected</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Election Information -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0 small">Election Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <p class="mb-2 small"><strong>Election Title:</strong> {{ $election->title }}</p>
                                    <p class="mb-2 small"><strong>Description:</strong> {{ $election->description }}</p>
                                    <p class="mb-2 small"><strong>Voting Period:</strong> {{ $election->voting_start->format('M d, Y h:i A') }} - {{ $election->voting_end->format('M d, Y h:i A') }}</p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <p class="mb-2 small"><strong>Total Candidates:</strong> {{ $election->candidates->count() }}</p>
                                    <p class="mb-2 small"><strong>Election Status:</strong> <span class="badge bg-success small">Completed</span></p>
                                    <p class="mb-2 small"><strong>Results Declared:</strong> {{ $election->results->first()?->declared_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            <span class="d-none d-sm-inline">Back to Elections</span>
                            <span class="d-inline d-sm-none">Back</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}
.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.winner-card {
    background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
    transition: all 0.3s ease;
}
.winner-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.candidate-card {
    transition: all 0.3s ease;
}
.candidate-card:hover {
    background-color: rgba(0,0,0,0.02);
}
.progress {
    border-radius: 10px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 10px;
        padding-right: 10px;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .stats-card {
        margin-bottom: 0;
    }
    
    .winner-card {
        margin-bottom: 0.5rem;
    }
    
    .candidate-card:last-child {
        border-bottom: none !important;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 5px;
        padding-right: 5px;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .stats-card {
        padding: 0.75rem !important;
    }
    
    .winner-card {
        padding: 0.75rem !important;
    }
    
    .candidate-card {
        padding: 0.75rem !important;
    }
}

@media (max-width: 480px) {
    .container-fluid {
        padding-left: 2px;
        padding-right: 2px;
    }
    
    .card-body {
        padding: 0.5rem;
    }
    
    .stats-card {
        padding: 0.5rem !important;
    }
    
    .winner-card {
        padding: 0.5rem !important;
    }
    
    .candidate-card {
        padding: 0.5rem !important;
    }
}
</style>
@endsection 