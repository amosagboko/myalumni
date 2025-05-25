@extends('layouts.elcom')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-bar-chart-fill me-2"></i>
                        Basic Election Results - {{ $election->title }}
                    </h4>
                    <a href="{{ route('elcom.elections.real-time-results', $election) }}" class="btn btn-primary">
                        <i class="bi bi-graph-up me-2"></i>
                        View Detailed Results
                    </a>
                </div>

                <div class="card-body">
                    <!-- Election Status -->
                    <div class="alert {{ $election->status === 'voting' ? 'alert-info' : 'alert-success' }} mb-4">
                        <h5 class="alert-heading">
                            <i class="bi {{ $election->status === 'voting' ? 'bi-clock' : 'bi-check-circle' }} me-2"></i>
                            Election Status: {{ ucfirst($election->status) }}
                        </h5>
                        @if($timeRemaining)
                            <p class="mb-0">
                                Time Remaining: <strong>{{ $timeRemaining }}</strong>
                            </p>
                        @endif
                    </div>

                    <!-- Basic Statistics Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="2" class="text-center h5 py-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Election Statistics
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold" style="width: 50%">
                                        <i class="bi bi-people-fill me-2"></i>
                                        Total Accredited Voters
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($totalAccredited) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        Total Votes Cast
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($totalVotes) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">
                                        <i class="bi bi-percent me-2"></i>
                                        Voter Turnout
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($voterTurnout, 2) }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Progress Bar for Voter Turnout -->
                    <div class="mt-4">
                        <h6 class="mb-3">Voter Turnout Progress</h6>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar {{ $voterTurnout >= 50 ? 'bg-success' : ($voterTurnout >= 25 ? 'bg-warning' : 'bg-danger') }}"
                                 role="progressbar"
                                 style="width: {{ min($voterTurnout, 100) }}%"
                                 aria-valuenow="{{ $voterTurnout }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                {{ number_format($voterTurnout, 2) }}%
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('elcom.elections.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Back to Elections
                        </a>
                        <a href="{{ route('elcom.elections.real-time-results', $election) }}" class="btn btn-primary">
                            <i class="bi bi-graph-up me-2"></i>
                            View Detailed Results
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress {
    background-color: #e9ecef;
    border-radius: 0.5rem;
}
.progress-bar {
    font-weight: 600;
    font-size: 0.9rem;
    line-height: 25px;
}
.table th {
    background-color: #f8f9fa;
}
</style>
@endsection 