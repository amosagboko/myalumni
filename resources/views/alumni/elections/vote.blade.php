@extends('layouts.alumni')

@section('content')
<div class="elections-hub-page elections-vote-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-600 mb-1">Cast Your Vote</h4>
                <p class="text-grey-500 font-xssss mb-0">{{ $election->title }}</p>
            </div>
            <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather-arrow-left me-1"></i> Back to Elections
            </a>
        </div>

        <div class="card-body p-4 w-100 border-0">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="elections-hub-stat text-center">
                        <div class="elections-hub-stat__label">Accredited voters</div>
                        <div class="elections-hub-stat__value fw-600">{{ number_format($totalAccredited) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="elections-hub-stat text-center">
                        <div class="elections-hub-stat__label">Voters register</div>
                        <div class="elections-hub-stat__value fw-600 text-info">{{ number_format($totalSubscribed + $totalExempted) }}</div>
                    </div>
                </div>
                @if($timeRemaining)
                    <div class="col-md-4">
                        <div class="elections-hub-stat text-center">
                            <div class="elections-hub-stat__label">Time remaining</div>
                            <div class="elections-hub-stat__value fw-600 text-primary">
                                <i class="feather-clock me-1"></i>{{ $timeRemaining }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="alert {{ $votingStatusAlert }} font-xssss mb-4">
                <div class="fw-600 mb-1"><i class="feather-info me-1"></i> Voting period</div>
                <p class="mb-0">{{ $votingStatusMessage }}</p>
                @if($election->voting_start && $election->voting_end)
                    <p class="mb-0 mt-2 text-muted">
                        {{ $election->voting_start->format('M d, Y h:i A') }}
                        –
                        {{ $election->voting_end->format('M d, Y h:i A') }}
                    </p>
                @endif
            </div>

            @if(! $isAccredited)
                <div class="alert alert-danger font-xssss mb-4">
                    <i class="feather-x-circle me-1"></i>
                    You are not accredited for this election. Complete accreditation before voting.
                    <div class="mt-3">
                        <a href="{{ route('alumni.elections.accreditation', $election) }}" class="btn btn-primary btn-sm">
                            <i class="feather-user-check me-1"></i> Go to accreditation
                        </a>
                    </div>
                </div>
            @elseif($hasVoted)
                <div class="alert alert-success font-xssss mb-0">
                    <i class="feather-check-circle me-1"></i>
                    You have already cast your vote in this election.
                    @if($votedAt)
                        <div class="mt-2 text-muted">Voted on {{ $votedAt->format('M d, Y h:i A') }}</div>
                    @endif
                </div>
            @elseif($votingPeriodActive)
                <form action="{{ route('alumni.elections.vote.preview', $election) }}" method="POST" id="votingForm">
                    @csrf

                    @foreach($offices as $office)
                        <div class="elections-ballot-office mb-4">
                            <h6 class="fw-600 font-xssss text-grey-900 mb-1">{{ $office->title }}</h6>
                            @if($office->description)
                                <p class="text-grey-500 font-xssss mb-3">{{ $office->description }}</p>
                            @endif

                            @if($office->candidates->isEmpty())
                                <div class="alert alert-info font-xssss mb-0">
                                    No approved candidates are available for this office.
                                </div>
                            @else
                                <div class="list-group list-group-flush">
                                    @foreach($office->candidates as $candidate)
                                        @include('alumni.elections.partials.ballot-candidate', [
                                            'office' => $office,
                                            'candidate' => $candidate,
                                        ])
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="alert alert-warning font-xssss">
                        <i class="feather-alert-triangle me-1"></i>
                        Once you submit your vote, it cannot be changed. Review your choices carefully.
                    </div>

                    <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                        <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="feather-arrow-left me-1"></i> Back to Elections
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="feather-eye me-1"></i> Preview votes
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumni-elections-hub.css') }}">
@endpush
@endsection
