@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-5 pt-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Vote - {{ $election->title }}</h3>
                </div>
                <div class="card-body">
                    <!-- Accreditation Statistics -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people-fill me-2"></i>
                                Accreditation Statistics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <div class="stats-card p-3">
                                        <h6 class="text-muted mb-2">Total Accredited Voters</h6>
                                        <h3 class="mb-0">{{ number_format($totalAccredited) }}</h3>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="stats-card p-3">
                                        <h6 class="text-muted mb-2">Voters Register</h6>
                                        <h3 class="mb-0 text-info">{{ number_format($totalSubscribed + $totalExempted) }}</h3>
                                    </div>
                                </div>
                                @if($timeRemaining)
                                    <div class="col-12 col-md-4">
                                        <div class="stats-card p-3">
                                            <h6 class="text-muted mb-2">Voting Time Remaining</h6>
                                            <h3 class="mb-0 text-primary">
                                                <i class="bi bi-clock me-2"></i>
                                                <span class="d-none d-sm-inline">{{ $timeRemaining }}</span>
                                                <span class="d-inline d-sm-none">{{ $timeRemaining }}</span>
                                            </h3>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @php
                        $alumni = auth()->user()->alumni;
                        $isAccredited = $election->accreditedVoters()->where('alumni_id', $alumni->id)->exists();
                        $hasVoted = $isAccredited && $election->accreditedVoters()
                            ->where('alumni_id', $alumni->id)
                            ->where('has_voted', true)
                            ->exists();
                        $votingPeriodActive = $election->status === 'voting' && 
                            now()->between($election->voting_start, $election->voting_end);
                    @endphp

                    <!-- Voting Period Status -->
                    <div class="alert {{ $votingPeriodActive ? 'alert-info' : 'alert-warning' }} mb-4">
                        <h5 class="alert-heading">
                            <i class="bi {{ $votingPeriodActive ? 'bi-info-circle' : 'bi-clock' }} me-2"></i>
                            Voting Period Status
                        </h5>
                        <p class="mb-0">
                            @if($votingPeriodActive)
                                Voting is currently active. You can cast your vote now.
                                <br>
                                <small class="d-block mt-2">
                                    Period: {{ $election->voting_start->format('M d, Y h:i A') }} - 
                                    {{ $election->voting_end->format('M d, Y h:i A') }}
                                </small>
                            @elseif($election->status === 'completed')
                                This election has been completed. Voting is no longer available.
                            @elseif($election->status === 'accreditation')
                                Voting has not started yet. The election is still in the accreditation phase.
                            @else
                                Voting period has not been scheduled for this election yet.
                            @endif
                        </p>
                    </div>

                    @if(!$isAccredited)
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle me-2"></i>
                            You are not accredited for this election. Please complete the accreditation process first.
                            <div class="mt-3">
                                <a href="{{ route('alumni.elections.accreditation', $election) }}" class="btn btn-primary w-100 w-md-auto">
                                    <i class="bi bi-person-check me-2"></i>
                                    Go to Accreditation
                                </a>
                            </div>
                        </div>
                    @elseif($hasVoted)
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            You have already cast your vote in this election.
                            @php
                                $voter = $election->accreditedVoters()
                                    ->where('alumni_id', $alumni->id)
                                    ->first();
                            @endphp
                            <small class="d-block mt-2">
                                Voted on: {{ $voter->voted_at->format('M d, Y h:i A') }}
                            </small>
                        </div>
                    @elseif($votingPeriodActive)
                        <form action="{{ route('alumni.elections.vote.preview', $election) }}" method="POST" id="votingForm">
                            @csrf
                            @foreach($offices as $office)
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">{{ $office->title }}</h5>
                                        <small class="text-muted d-block d-md-inline">{{ $office->description }}</small>
                                    </div>
                                    <div class="card-body">
                                        @if($office->candidates->isEmpty())
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i>
                                                No candidates are available for this office.
                                            </div>
                                        @else
                                            <div class="list-group">
                                                @foreach($office->candidates as $candidate)
                                                    <label class="list-group-item list-group-item-action">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" 
                                                                name="votes[{{ $office->id }}]" 
                                                                value="{{ $candidate->id }}"
                                                                required>
                                                            <div class="d-flex align-items-start">
                                                                @if($candidate->passport)
                                                                    <img src="{{ asset('storage/' . $candidate->passport) }}" 
                                                                        alt="Candidate" 
                                                                        class="rounded-circle me-3 mt-1"
                                                                        style="width: 48px; height: 48px; object-fit: cover; flex-shrink: 0;">
                                                                @endif
                                                                <div class="flex-grow-1">
                                                                    <h6 class="mb-1">{{ $candidate->alumni->user->name }}</h6>
                                                                    <small class="text-muted d-block mb-2">
                                                                        {{ $candidate->alumni->matriculation_number }}
                                                                    </small>
                                                                    @if($candidate->manifesto)
                                                                        <div class="mt-2">
                                                                            <button type="button" 
                                                                                class="btn btn-sm btn-outline-primary"
                                                                                data-bs-toggle="modal" 
                                                                                data-bs-target="#manifestoModal{{ $candidate->id }}">
                                                                                <i class="bi bi-file-text me-1"></i>
                                                                                <span class="d-none d-sm-inline">View Manifesto</span>
                                                                                <span class="d-inline d-sm-none">Manifesto</span>
                                                                            </button>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </label>

                                                    <!-- Manifesto Modal -->
                                                    @if($candidate->manifesto)
                                                        <div class="modal fade" id="manifestoModal{{ $candidate->id }}" tabindex="-1">
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">
                                                                            Manifesto - {{ $candidate->alumni->user->name }}
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="manifesto-content">
                                                                            {!! nl2br(e($candidate->manifesto)) !!}
                                                                        </div>
                                                                        @if($candidate->documents)
                                                                            <div class="mt-4">
                                                                                <h6>Supporting Documents:</h6>
                                                                                <ul class="list-unstyled">
                                                                                    @foreach($candidate->documents as $document)
                                                                                        <li class="mb-2">
                                                                                            <a href="{{ asset('storage/' . $document) }}" 
                                                                                                target="_blank" 
                                                                                                class="btn btn-sm btn-outline-secondary">
                                                                                                <i class="bi bi-file-earmark me-2"></i>
                                                                                                <span class="d-none d-sm-inline">View Document</span>
                                                                                                <span class="d-inline d-sm-none">Document</span>
                                                                                            </a>
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Please note: Once you submit your vote, it cannot be changed. Make sure to review your choices carefully.
                            </div>

                            <div class="card-footer">
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                    <a href="{{ route('alumni.elections') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>
                                        <span class="d-none d-sm-inline">Back to Elections</span>
                                        <span class="d-inline d-sm-none">Back</span>
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-eye me-2"></i>
                                        <span class="d-none d-sm-inline">Preview Votes</span>
                                        <span class="d-inline d-sm-none">Preview</span>
                                    </button>
                                </div>
                            </div>
                        </form>
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
    
    .stats-card {
        text-align: center;
    }
    
    .stats-card h3 {
        font-size: 1.5rem;
    }
    
    .stats-card h6 {
        font-size: 0.875rem;
    }
    
    .alert-heading {
        font-size: 1.1rem;
    }
    
    .card-title {
        font-size: 1.25rem;
    }
    
    .list-group-item {
        padding: 0.75rem;
    }
    
    .form-check-input {
        margin-top: 0.25rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-title {
        font-size: 1.1rem;
    }
    
    .alert {
        padding: 0.75rem;
    }
    
    .alert-heading {
        font-size: 1rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .card-header {
        padding: 0.75rem;
    }
    
    .stats-card {
        padding: 1rem !important;
    }
    
    .stats-card h3 {
        font-size: 1.25rem;
    }
    
    .list-group-item {
        padding: 0.5rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
}

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
</style>
@endsection 