@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-5 pt-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Available Elections</h3>
                </div>
                <div class="card-body">
                    @if($elections->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
                            <p class="text-muted mb-0">No elections are currently available.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($elections as $election)
                                <div class="col-12 mb-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title fw-bold mb-3">{{ $election->title }}</h5>
                                            
                                            @if($election->offices->isEmpty())
                                                <div class="alert alert-info mb-3">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    No offices available for this election.
                                                </div>
                                            @else
                                                <div class="mb-3">
                                                    <h6 class="text-muted mb-2">Available Offices:</h6>
                                                    @foreach($election->offices as $office)
                                                        <div class="card mb-2 border-light">
                                                            <div class="card-body py-2">
                                                                <div class="row align-items-center">
                                                                    <div class="col-12 col-md-6 mb-2 mb-md-0">
                                                                        <span class="fw-medium">{{ $office->title }}</span>
                                                                    </div>
                                                                    <div class="col-12 col-md-6">
                                                                        <div class="d-flex flex-wrap gap-1">
                                                                            <a href="{{ route('alumni.elections.expression-of-interest.form', [$election, $office]) }}" 
                                                                               class="btn btn-sm btn-success">
                                                                                <i class="bi bi-pencil-square me-1"></i>
                                                                                <span class="d-none d-sm-inline">Express Interest</span>
                                                                                <span class="d-inline d-sm-none">Interest</span>
                                                                            </a>
                                                                            <a href="{{ route('alumni.elections.published-candidates', [$election, $office]) }}" 
                                                                               class="btn btn-sm btn-outline-primary">
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
                                            
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('alumni.elections.accreditation', $election) }}" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="bi bi-person-check me-1"></i>
                                                    <span class="d-none d-sm-inline">Accreditation</span>
                                                    <span class="d-inline d-sm-none">Accredit</span>
                                                </a>
                                                <a href="{{ route('alumni.elections.vote', $election) }}" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="bi bi-check2-square me-1"></i>
                                                    Vote
                                                </a>
                                                <a href="{{ route('alumni.elections.results', $election) }}" 
                                                   class="btn btn-secondary btn-sm">
                                                    <i class="bi bi-bar-chart me-1"></i>
                                                    <span class="d-none d-sm-inline">Results</span>
                                                    <span class="d-inline d-sm-none">Results</span>
                                                </a>
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
@media (max-width: 768px) {
    .container-fluid {
        margin-left: 0 !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-title {
        font-size: 1.25rem;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .d-flex.flex-wrap.gap-2 {
        flex-direction: column;
    }
    
    .d-flex.flex-wrap.gap-2 .btn {
        margin-bottom: 0.5rem;
    }
}
</style>
@endsection 