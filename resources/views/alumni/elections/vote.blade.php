@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-7" style="margin-left: 300px; max-width: 900px;">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Vote - {{ $election->title }}</h3>
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
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="stats-card p-3">
                                        <h6 class="text-muted mb-2">Total Accredited Voters</h6>
                                        <h3 class="mb-0">{{ number_format($totalAccredited) }}</h3>
                                    </div>
                                </div>
                                @if($timeRemaining)
                                    <div class="col-md-6">
                                        <div class="stats-card p-3">
                                            <h6 class="text-muted mb-2">Voting Time Remaining</h6>
                                            <h3 class="mb-0 text-primary">
                                                <i class="bi bi-clock me-2"></i>
                                                {{ $timeRemaining }}
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
                                <a href="{{ route('alumni.elections.accreditation', $election) }}" class="btn btn-primary">
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
                                        <small class="text-muted">{{ $office->description }}</small>
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
                                                            <div class="d-flex align-items-center">
                                                                @if($candidate->passport)
                                                                    <img src="{{ asset('storage/' . $candidate->passport) }}" 
                                                                        alt="Candidate" 
                                                                        class="rounded-circle me-3"
                                                                        style="width: 48px; height: 48px; object-fit: cover;">
                                                                @endif
                                                                <div>
                                                                    <h6 class="mb-1">{{ $candidate->alumni->user->name }}</h6>
                                                                    <small class="text-muted">
                                                                        {{ $candidate->alumni->matriculation_number }}
                                                                    </small>
                                                                    @if($candidate->manifesto)
                                                                        <div class="mt-2">
                                                                            <button type="button" 
                                                                                class="btn btn-sm btn-outline-primary"
                                                                                data-bs-toggle="modal" 
                                                                                data-bs-target="#manifestoModal{{ $candidate->id }}">
                                                                                View Manifesto
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
                                                                                        <li>
                                                                                            <a href="{{ asset('storage/' . $document) }}" 
                                                                                                target="_blank" 
                                                                                                class="btn btn-sm btn-outline-secondary">
                                                                                                <i class="bi bi-file-earmark me-2"></i>
                                                                                                View Document
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
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('alumni.elections') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Elections
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>Preview Votes
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
@endsection 