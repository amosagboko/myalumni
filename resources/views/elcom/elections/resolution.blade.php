@extends('layouts.elcom')

@push('styles')
<style>
    .resolution-deep-green {
        color: #0a3622 !important;
    }
    .resolution-deep-green-border {
        border-color: #0a3622 !important;
    }
    .resolution-deep-green-bg {
        background-color: #0a3622 !important;
        color: #fff !important;
    }
    .resolution-deep-green-bg h5,
    .resolution-deep-green-bg h5 i {
        color: #fff !important;
    }
    .resolution-deep-green-badge {
        background-color: #0a3622 !important;
        color: #fff !important;
    }
    .resolution-winners-row:nth-child(even) {
        background-color: #f4faf6;
    }
    .resolution-winners-row:hover {
        background-color: #e8f5ec;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-0">Election Resolution</h4>
                        <small class="text-muted">{{ $election->title }}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('elcom.elections.show', $election) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Election
                        </a>
                        @if(in_array($election->status, ['incomplete', 'completed', 'archived']))
                            <a href="{{ route('elcom.elections.real-time-results', $election) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-bar-chart me-1"></i> View Results
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($election->isIncomplete())
                        <div class="alert alert-warning border-warning">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 mt-1"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Election Incomplete</h5>
                                    <p class="mb-2">
                                        {{ $resolution['pending_count'] }} office(s) still require resolution via by-election.
                                        The <strong>ELCOM chairman role is retained</strong> until every seat has a declared winner.
                                    </p>
                                    <p class="mb-0 small text-muted">
                                        Tied offices → runoff between tied candidates (no EOI).
                                        Uncontested offices → new EOI required.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($election->status === 'completed')
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            All offices have declared winners. This election is complete.
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card resolution-deep-green-border h-100">
                                <div class="card-body text-center">
                                    <h2 class="resolution-deep-green mb-1">{{ $resolution['decided']->count() }}</h2>
                                    <div class="text-muted">Decided</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-danger h-100">
                                <div class="card-body text-center">
                                    <h2 class="text-danger mb-1">{{ $resolution['tied']->count() }}</h2>
                                    <div class="text-muted">Tied (runoff needed)</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-secondary h-100">
                                <div class="card-body text-center">
                                    <h2 class="text-secondary mb-1">{{ $resolution['uncontested']->count() }}</h2>
                                    <div class="text-muted">Uncontested (EOI needed)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($resolution['tied']->isNotEmpty())
                        <div class="card mb-4 border-danger">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Tied Offices</h5>
                            </div>
                            <div class="card-body p-0">
                                @foreach($resolution['tied'] as $item)
                                    <div class="p-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                            <h6 class="mb-0 fw-bold">{{ $item['office']->title }}</h6>
                                            <span class="badge bg-danger">TIE — {{ $item['top_vote_count'] }} votes each</span>
                                        </div>
                                        <div class="row g-3">
                                            @foreach($item['tied_candidates'] as $candidate)
                                                <div class="col-md-6">
                                                    <div class="border border-danger rounded p-3 bg-danger bg-opacity-10 h-100">
                                                        <div class="fw-semibold">{{ $candidate->alumni->user->name }}</div>
                                                        <div class="small text-muted">{{ $candidate->alumni->matriculation_number }}</div>
                                                        <div class="mt-2">
                                                            <span class="badge bg-danger">{{ number_format($candidate->votes_count) }} votes</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="small text-muted mb-0 mt-3">
                                            <i class="bi bi-info-circle me-1"></i>
                                            By-election: runoff between these candidates only (no EOI).
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($resolution['uncontested']->isNotEmpty())
                        <div class="card mb-4 border-secondary">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="bi bi-dash-circle me-2"></i>Uncontested Offices</h5>
                            </div>
                            <div class="card-body p-0">
                                @foreach($resolution['uncontested'] as $item)
                                    <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div>
                                            <h6 class="mb-1 fw-bold">{{ $item['office']->title }}</h6>
                                            <p class="text-muted mb-0 small">No approved candidate appeared on the ballot.</p>
                                        </div>
                                        <span class="badge bg-secondary">Pending by-election (EOI required)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($resolution['decided']->isNotEmpty())
                        <div class="card mb-4 resolution-deep-green-border">
                            <div class="card-header resolution-deep-green-bg">
                                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Declared Winners</h5>
                            </div>
                            <div class="card-body p-0">
                                @foreach($resolution['decided'] as $item)
                                    <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-2 resolution-winners-row {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div>
                                            <h6 class="mb-1 resolution-deep-green">{{ $item['office']->title }}</h6>
                                            @if($item['winner'])
                                                <div class="fw-semibold">{{ $item['winner']->alumni->user->name }}</div>
                                                <div class="small text-muted">{{ $item['winner']->alumni->matriculation_number }}</div>
                                            @endif
                                        </div>
                                        <span class="badge resolution-deep-green-badge">
                                            {{ number_format($item['top_vote_count']) }} votes
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($resolution['offices_in_by_election']->isNotEmpty())
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>By-Election In Progress</h5>
                            </div>
                            <div class="card-body">
                                @if($resolution['active_by_election'])
                                    <p class="mb-3">
                                        <a href="{{ route('elcom.elections.show', $resolution['active_by_election']) }}" class="alert-link">
                                            {{ $resolution['active_by_election']->title }}
                                        </a>
                                        — status: <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $resolution['active_by_election']->status)) }}</span>
                                    </p>
                                @endif
                                <ul class="mb-0">
                                    @foreach($resolution['offices_in_by_election'] as $office)
                                        <li>{{ $office->title }}
                                            @if($office->isTied())
                                                <span class="badge bg-danger">Runoff</span>
                                            @else
                                                <span class="badge bg-secondary">EOI</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if($election->isIncomplete() && !$resolution['active_by_election'] && $resolution['has_pending'])
                        <div class="d-grid gap-2 mb-4">
                            <a href="{{ route('elcom.elections.schedule-by-election', $election) }}" class="btn btn-warning">
                                <i class="bi bi-calendar-event me-2"></i>Schedule By-Election
                            </a>
                        </div>
                    @elseif($election->isIncomplete() && $resolution['active_by_election'])
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            A by-election is in progress. Manage it from the
                            <a href="{{ route('elcom.elections.show', $resolution['active_by_election']) }}">by-election dashboard</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
