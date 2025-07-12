@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0 h4 h-md-3">Available Elections</h3>
                </div>
                <div class="card-body">
                    @if($elections->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
                            <p class="text-muted mb-0">No elections are currently available.</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($elections as $election)
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <!-- Election Header -->
                                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                                <h5 class="card-title fw-bold mb-2 mb-md-0 h5 h-md-4">{{ $election->title }}</h5>
                                                <span class="badge {{ $election->status === 'completed' ? 'bg-success' : 'bg-primary' }} fs-6">
                                                    {{ ucfirst($election->status) }}
                                                </span>
                                            </div>
                                            
                                            <!-- Election Description -->
                                            @if($election->description)
                                                <p class="text-muted mb-3 small">{{ Str::limit($election->description, 150) }}</p>
                                            @endif
                                            
                                            @if($election->offices->isEmpty())
                                                <div class="alert alert-info mb-3">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    No offices available for this election.
                                                </div>
                                            @else
                                                <!-- Offices Section -->
                                                <div class="mb-3">
                                                    <h6 class="text-muted mb-2 small">Available Offices:</h6>
                                                    @foreach($election->offices as $office)
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
                                                                            <a href="{{ route('alumni.elections.expression-of-interest.form', [$election, $office]) }}" 
                                                                               class="btn btn-sm btn-success w-100 w-md-auto">
                                                                                <i class="bi bi-pencil-square me-1"></i>
                                                                                <span class="d-none d-sm-inline">Express Interest</span>
                                                                                <span class="d-inline d-sm-none">Interest</span>
                                                                            </a>
                                                                            <a href="{{ route('alumni.elections.published-candidates', [$election, $office]) }}" 
                                                                               class="btn btn-sm btn-outline-primary w-100 w-md-auto">
                                                                                <i class="bi bi-people me-1"></i>
                                                                                <span class="d-none d-sm-inline">View Candidates</span>
                                                                                <span class="d-inline d-sm-none">Candidates</span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            
                                            <!-- Action Buttons -->
                                            <div class="d-flex flex-column flex-md-row gap-2">
                                                @if($election->status === 'completed')
                                                    <!-- Completed Election - Show Results prominently -->
                                                    <a href="{{ route('alumni.elections.results', $election) }}" 
                                                       class="btn btn-success w-100 w-md-auto">
                                                        <i class="bi bi-trophy me-2"></i>
                                                        <span class="d-none d-sm-inline">View Results</span>
                                                        <span class="d-inline d-sm-none">Results</span>
                                                    </a>
                                                @else
                                                    <!-- Active Election - Show normal buttons -->
                                                    <a href="{{ route('alumni.elections.accreditation', $election) }}" 
                                                       class="btn btn-info w-100 w-md-auto">
                                                        <i class="bi bi-person-check me-2"></i>
                                                        <span class="d-none d-sm-inline">Accreditation</span>
                                                        <span class="d-inline d-sm-none">Accredit</span>
                                                    </a>
                                                    <a href="{{ route('alumni.elections.vote', $election) }}" 
                                                       class="btn btn-primary w-100 w-md-auto">
                                                        <i class="bi bi-check2-square me-2"></i>
                                                        Vote
                                                    </a>
                                                    @if($election->status === 'voting')
                                                        <a href="{{ route('alumni.elections.results', $election) }}" 
                                                           class="btn btn-secondary w-100 w-md-auto">
                                                            <i class="bi bi-bar-chart me-2"></i>
                                                            <span class="d-none d-sm-inline">Live Results</span>
                                                            <span class="d-inline d-sm-none">Results</span>
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
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
    .container-fluid {
        margin-left: 0 !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .h-md-3 {
        font-size: 1.5rem !important;
    }
    
    .h-md-4 {
        font-size: 1.25rem !important;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
    }
    
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }
    
    .badge {
        font-size: 0.75rem !important;
        padding: 0.5rem 0.75rem;
    }
    
    .card-title {
        font-size: 1.1rem;
        line-height: 1.3;
    }
    
    .text-muted {
        font-size: 0.875rem;
    }
    
    .small {
        font-size: 0.8rem;
    }
    
    .d-flex.flex-column.flex-md-row {
        flex-direction: column !important;
    }
    
    .d-flex.flex-column.flex-md-row .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .d-flex.flex-column.flex-md-row .btn:last-child {
        margin-bottom: 0;
    }
}

@media (max-width: 575.98px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .btn {
        font-size: 0.8rem;
        padding: 0.625rem 0.875rem;
    }
    
    .card-title {
        font-size: 1rem;
    }
    
    .badge {
        font-size: 0.7rem !important;
        padding: 0.375rem 0.625rem;
    }
}

@media (min-width: 768px) {
    .w-md-auto {
        width: auto !important;
    }
    
    .d-flex.flex-column.flex-md-row {
        flex-direction: row !important;
    }
    
    .d-flex.flex-column.flex-md-row .btn {
        margin-bottom: 0;
    }
}
</style>
@endsection 