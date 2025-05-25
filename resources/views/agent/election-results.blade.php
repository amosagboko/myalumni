@extends('layouts.app')

@section('content')
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('agent.dashboard') }}" class="btn btn-outline-secondary btn-sm me-3">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                        <h3 class="card-title mb-0">Election Results</h3>
                    </div>
                    <span class="badge bg-primary">{{ $election->title }}</span>
                </div>

                <div class="card-body">
                    <!-- Election Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Votes Cast</h5>
                                    <h2 class="mb-0">{{ $totalVotes }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Your Candidates</h5>
                                    <h2 class="mb-0">{{ $candidates->count() }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Votes for Your Candidates</h5>
                                    <h2 class="mb-0">{{ $candidates->sum('vote_count') }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results by Office -->
                    @foreach($candidates->groupBy('office_id') as $officeCandidates)
                        @php
                            $office = $officeCandidates->first()->office;
                            $sortedCandidates = $officeCandidates->sortByDesc('vote_count');
                            $totalOfficeVotes = $officeCandidates->sum('vote_count');
                        @endphp
                        
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">{{ $office->title }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Candidate</th>
                                                <th>Votes</th>
                                                <th>Percentage</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sortedCandidates as $candidate)
                                                @php
                                                    $percentage = $totalOfficeVotes > 0 
                                                        ? round(($candidate->vote_count / $totalOfficeVotes) * 100, 1)
                                                        : 0;
                                                    $isWinner = $loop->first && $candidate->vote_count > 0;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($candidate->passport)
                                                                <img src="{{ asset('storage/' . $candidate->passport) }}" 
                                                                     alt="Passport" 
                                                                     class="rounded-circle me-2"
                                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                            @endif
                                                            <div>
                                                                <div class="fw-medium">{{ $candidate->alumni->user->name }}</div>
                                                                <small class="text-muted">{{ $candidate->alumni->matriculation_number }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <strong>{{ $candidate->vote_count }}</strong>
                                                            @if($isWinner)
                                                                <span class="badge bg-success ms-2">Winner</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar {{ $isWinner ? 'bg-success' : 'bg-primary' }}" 
                                                                 role="progressbar" 
                                                                 style="width: {{ $percentage }}%"
                                                                 aria-valuenow="{{ $percentage }}" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100">
                                                                {{ $percentage }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($isWinner)
                                                            <span class="badge bg-success">Elected</span>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 