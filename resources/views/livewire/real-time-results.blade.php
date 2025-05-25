<div wire:poll.30s="updateResults">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Real-Time Election Results</h1>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('elcom.elections.print-full-results', $election) }}" 
                class="btn btn-outline-primary btn-sm" 
                target="_blank"
                title="Print Full Results">
                <i class="fas fa-print me-1"></i>
                Print Full Results
            </a>
            <a href="{{ route('elcom.elections.print-winners', $election) }}" 
                class="btn btn-outline-success btn-sm" 
                target="_blank"
                title="Print Winners List">
                <i class="fas fa-trophy me-1"></i>
                Print Winners
            </a>
            <span class="badge bg-success me-2">
                <i class="fas fa-circle live-indicator"></i> LIVE
            </span>
            <span class="refresh-indicator">Auto-updating</span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6 class="text-muted mb-2">Total Accredited Voters</h6>
                <h3 class="mb-0">{{ number_format($totalAccredited) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6 class="text-muted mb-2">Total Votes Cast</h6>
                <h3 class="mb-0">{{ number_format($totalVotes) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6 class="text-muted mb-2">Voter Turnout</h6>
                <h3 class="mb-0">{{ $voterTurnout }}%</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6 class="text-muted mb-2">Time Remaining</h6>
                <h3 class="mb-0">{{ $timeRemaining }}</h3>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($offices as $office)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ $office['title'] }}</h5>
                    </div>
                    <div class="card-body">
                        @foreach($office['candidates'] as $index => $candidate)
                            <div class="candidate-card p-3 mb-3 position-relative {{ $index === 0 ? 'border-success' : '' }}">
                                @if($index === 0)
                                    <div class="winner-badge">Leading</div>
                                @endif
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">{{ $candidate['name'] }}</h6>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ number_format($candidate['votes']) }} votes</span>
                                        <span>{{ number_format($candidate['percentage'], 1) }}%</span>
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $candidate['percentage'] }}%"
                                         aria-valuenow="{{ $candidate['percentage'] }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('styles')
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
    .progress {
        height: 8px;
        border-radius: 4px;
    }
    .candidate-card {
        border-left: 4px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .candidate-card:hover {
        border-left-color: #007bff;
        background-color: #f8f9fa;
    }
    .candidate-card.border-success {
        border-left-color: #28a745;
    }
    .winner-badge {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
    }
    .refresh-indicator {
        font-size: 0.875rem;
        color: #6c757d;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    .live-indicator {
        animation: pulse 2s infinite;
    }
</style>
@endpush 