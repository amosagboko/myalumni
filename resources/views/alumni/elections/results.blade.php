@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-7" style="margin-left: 300px; max-width: 1000px;">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">
                        <i class="bi bi-trophy me-2 text-warning"></i>
                        Election Results - {{ $election->title }}
                    </h3>
                    <p class="text-muted mb-0">Final results declared on {{ $election->results->first()?->declared_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                </div>
                <div class="card-body">
                    <!-- Election Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stats-card p-3 text-center">
                                <h6 class="text-muted mb-2">Total Accredited Voters</h6>
                                <h3 class="mb-0 text-primary">{{ number_format($totalAccredited) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card p-3 text-center">
                                <h6 class="text-muted mb-2">Total Votes Cast</h6>
                                <h3 class="mb-0 text-success">{{ number_format($totalVotes) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card p-3 text-center">
                                <h6 class="text-muted mb-2">Voter Turnout</h6>
                                <h3 class="mb-0 text-info">{{ $voterTurnout }}%</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card p-3 text-center">
                                <h6 class="text-muted mb-2">Offices Contested</h6>
                                <h3 class="mb-0 text-warning">{{ $election->offices->count() }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Winners Section -->
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-trophy-fill me-2"></i>
                                Election Winners
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($officeResults as $officeResult)
                                    @php
                                        $winner = $officeResult['candidates']->where('is_winner', true)->first();
                                    @endphp
                                    @if($winner)
                                        <div class="col-md-6 mb-3">
                                            <div class="winner-card p-3 border border-success rounded">
                                                <div class="d-flex align-items-center">
                                                    @if($winner['candidate']->passport)
                                                        <img src="{{ asset('storage/' . $winner['candidate']->passport) }}" 
                                                             alt="Winner" 
                                                             class="rounded-circle me-3"
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    @endif
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 text-success">
                                                            <i class="bi bi-trophy-fill me-2"></i>
                                                            {{ $officeResult['office']->title }}
                                                        </h6>
                                                        <h5 class="mb-1">{{ $winner['candidate']->alumni->user->name }}</h5>
                                                        <p class="text-muted mb-1">{{ $winner['candidate']->alumni->matriculation_number }}</p>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="badge bg-success">{{ number_format($winner['votes']) }} votes</span>
                                                            <span class="text-muted">{{ $winner['percentage'] }}% of total votes</span>
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

                    <!-- Detailed Results by Office -->
                    <h5 class="mb-3">
                        <i class="bi bi-list-ul me-2"></i>
                        Detailed Results by Office
                    </h5>
                    
                    @foreach($officeResults as $officeResult)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">{{ $officeResult['office']->title }}</h6>
                                <small class="text-muted">Total votes cast: {{ number_format($officeResult['total_votes']) }}</small>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
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
                    @endforeach

                    <!-- Election Information -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">Election Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Election Title:</strong> {{ $election->title }}</p>
                                    <p><strong>Description:</strong> {{ $election->description }}</p>
                                    <p><strong>Voting Period:</strong> {{ $election->voting_start->format('M d, Y h:i A') }} - {{ $election->voting_end->format('M d, Y h:i A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total Candidates:</strong> {{ $election->candidates->count() }}</p>
                                    <p><strong>Election Status:</strong> <span class="badge bg-success">Completed</span></p>
                                    <p><strong>Results Declared:</strong> {{ $election->results->first()?->declared_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
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
.progress {
    border-radius: 10px;
}
</style>
@endsection 