@extends('layouts.alumni')

@section('content')
@php
    $resultsService = app(\App\Services\Alumni\AlumniElectionResultsService::class);
@endphp

<div class="elections-hub-page elections-results-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-600 mb-1">Election Results</h4>
                <p class="text-grey-500 font-xssss mb-1">{{ $election->title }}</p>
                @if($declaredAt)
                    <p class="text-grey-500 font-xssss mb-0">
                        Declared {{ $declaredAt->format('M d, Y h:i A') }}
                    </p>
                @endif
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="feather-arrow-left me-1"></i> Back to Elections
                </a>
            </div>
        </div>

        <div class="card-body p-4 w-100 border-0">
            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

            @if($election->isIncomplete())
                <div class="alert alert-warning font-xssss mb-4">
                    <i class="feather-alert-triangle me-1"></i>
                    <strong>Election incomplete.</strong>
                    {{ $resolution['pending_count'] }} office(s) are tied or uncontested and will be resolved in a by-election.
                    Winners shown below are only for offices with a clear result.
                </div>
            @endif

            @if($election->isArchived())
                <div class="alert alert-secondary font-xssss mb-4">
                    <i class="feather-archive me-1"></i>
                    <strong>Archived election</strong>
                    @if($election->archived_at)
                        — archived on {{ $election->archived_at->format('M d, Y') }}.
                    @endif
                    Results are read-only historical records.
                </div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="elections-hub-stat text-center">
                        <div class="elections-hub-stat__label">Accredited voters</div>
                        <div class="elections-hub-stat__value fw-600">{{ number_format($totalAccredited) }}</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="elections-hub-stat text-center">
                        <div class="elections-hub-stat__label">Votes cast</div>
                        <div class="elections-hub-stat__value fw-600 text-success">{{ number_format($totalVotes) }}</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="elections-hub-stat text-center">
                        <div class="elections-hub-stat__label">Turnout</div>
                        <div class="elections-hub-stat__value fw-600 text-info">{{ $voterTurnout }}%</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="elections-hub-stat text-center">
                        <div class="elections-hub-stat__label">Offices</div>
                        <div class="elections-hub-stat__value fw-600">{{ $election->offices->count() }}</div>
                    </div>
                </div>
            </div>

            @php
                $winners = $officeResults->map(function ($officeResult) {
                    $winner = $officeResult['candidates']->firstWhere('is_winner', true);
                    return $winner ? ['office' => $officeResult['office'], 'winner' => $winner] : null;
                })->filter();
            @endphp

            @if($winners->isNotEmpty())
                <h6 class="fw-600 font-xssss text-grey-900 text-uppercase mb-3">Winners</h6>
                <div class="row g-3 mb-4">
                    @foreach($winners as $item)
                        <div class="col-md-6">
                            <div class="elections-results-winner">
                                <div class="d-flex align-items-center gap-3">
                                    @if($item['winner']['candidate']->passport)
                                        <img src="{{ asset('storage/' . $item['winner']['candidate']->passport) }}"
                                             alt="Winner photo"
                                             class="rounded-circle elections-results-winner__avatar">
                                    @else
                                        <div class="elections-results-winner__avatar elections-results-winner__avatar--placeholder rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="feather-user text-grey-500"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="text-success font-xsssss text-uppercase fw-600 mb-1">
                                            <i class="feather-award me-1"></i>{{ $item['office']->title }}
                                        </div>
                                        <div class="fw-600 font-xssss text-grey-900">
                                            {{ $resultsService->candidateName($item['winner']['candidate']) }}
                                        </div>
                                        <div class="text-grey-500 font-xssss">
                                            {{ $resultsService->candidateMatric($item['winner']['candidate']->alumni) }}
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <span class="badge bg-success">{{ number_format($item['winner']['votes']) }} votes</span>
                                            <span class="text-grey-500 font-xssss">{{ $item['winner']['percentage'] }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($resolution['has_pending'] ?? false)
                <div class="card border-warning mb-4">
                    <div class="card-header bg-warning bg-opacity-25 border-0 py-3">
                        <h6 class="fw-600 mb-0 font-xssss">
                            <i class="feather-clock me-1"></i> Pending by-election
                        </h6>
                    </div>
                    <div class="card-body py-3">
                        @foreach($resolution['tied'] as $item)
                            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <h6 class="font-xssss text-danger mb-2">
                                    {{ $item['office']->title }}
                                    <span class="badge bg-danger">Tie</span>
                                </h6>
                                <ul class="mb-0 font-xssss text-grey-700">
                                    @foreach($item['tied_candidates'] as $candidate)
                                        <li>{{ $resultsService->candidateName($candidate) }} ({{ number_format($candidate->votes_count) }} votes)</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                        @foreach($resolution['uncontested'] as $item)
                            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <h6 class="font-xssss text-grey-700 mb-1">
                                    {{ $item['office']->title }}
                                    <span class="badge bg-secondary">Uncontested</span>
                                </h6>
                                <p class="font-xssss text-grey-500 mb-0">No candidate contested this office.</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <h6 class="fw-600 font-xssss text-grey-900 text-uppercase mb-3">Results by office</h6>
            @foreach($officeResults as $officeResult)
                @include('alumni.elections.partials.results-office', ['officeResult' => $officeResult])
            @endforeach

            <div class="border rounded-3 p-3 mt-2 bg-light">
                <h6 class="fw-600 font-xssss text-grey-900 mb-3">Election information</h6>
                <div class="row g-3 font-xssss">
                    <div class="col-md-6">
                        <div class="mb-2"><span class="text-grey-500">Title:</span> {{ $election->title }}</div>
                        @if($election->description)
                            <div class="mb-2"><span class="text-grey-500">Description:</span> {{ $election->description }}</div>
                        @endif
                        @if($election->voting_start && $election->voting_end)
                            <div class="mb-0">
                                <span class="text-grey-500">Voting period:</span>
                                {{ $election->voting_start->format('M d, Y h:i A') }} – {{ $election->voting_end->format('M d, Y h:i A') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2"><span class="text-grey-500">Candidates:</span> {{ $election->candidates->count() }}</div>
                        <div class="mb-2">
                            <span class="text-grey-500">Status:</span>
                            <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="mb-0">
                            <span class="text-grey-500">Results declared:</span>
                            {{ $declaredAt?->format('M d, Y h:i A') ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumni-elections-hub.css') }}">
@endpush
@endsection
