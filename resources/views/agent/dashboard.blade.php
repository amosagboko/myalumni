@extends('layouts.alumni')

@section('content')
<x-admin.surface-styles />

<div class="main-content right-chat-active admin-surface">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left pe-0">
            <div class="row">
                <div class="col-12">

                    <div class="ads-page-header">
                        <div>
                            <h1 class="ads-page-title">Agent Dashboard</h1>
                            <p class="ads-page-subtitle">Candidate screening and election activity.</p>
                        </div>
                    </div>

                    <div class="ads-section">
                        <div class="ads-section-card">
                            <h2 class="ads-section-title">Candidates</h2>
                            <div class="ads-stats">
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Total</span>
                                            <span class="ads-stat-value">{{ number_format($candidateStats['total']) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="users"></i></span>
                                    </div>
                                </div>
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Pending</span>
                                            <span class="ads-stat-value">{{ number_format($candidateStats['pending']) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="clock"></i></span>
                                    </div>
                                </div>
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Approved</span>
                                            <span class="ads-stat-value">{{ number_format($candidateStats['approved']) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="check-circle"></i></span>
                                    </div>
                                </div>
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Unpaid fees</span>
                                            <span class="ads-stat-value">{{ number_format($candidateStats['unpaid']) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="credit-card"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="ads-section mb-0">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">Active elections</h2>
                                    @if($activeElections->isEmpty())
                                        <p class="ads-empty-inline mb-0">No active elections found.</p>
                                    @else
                                        @foreach($activeElections as $election)
                                            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: var(--adt-border) !important;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <strong class="small">{{ $election->title }}</strong>
                                                    <span class="badge rounded-pill bg-light text-dark border">{{ ucfirst($election->status) }}</span>
                                                </div>
                                                <div class="ads-compact-table-wrap">
                                                    <table class="ads-compact-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Candidate</th>
                                                                <th>Office</th>
                                                                <th>Status</th>
                                                                <th>Payment</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($election->candidates as $candidate)
                                                                <tr>
                                                                    <td>{{ $candidate->alumni->user->name ?? '—' }}</td>
                                                                    <td>{{ $candidate->office->title ?? '—' }}</td>
                                                                    <td>{{ $candidate->status_label ?? ucfirst($candidate->status) }}</td>
                                                                    <td>{{ $candidate->has_paid_screening_fee ? 'Paid' : 'Unpaid' }}</td>
                                                                    <td class="text-end">
                                                                        <a href="{{ route('agent.candidates.show', [$election, $candidate]) }}" class="btn btn-sm btn-outline-primary py-0">View</a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="ads-section mb-0">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">Recent activity</h2>
                                    @if($recentActivities->isEmpty())
                                        <p class="ads-empty-inline mb-0">No recent activity.</p>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach($recentActivities as $activity)
                                                <li class="py-2 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: var(--adt-border) !important;">
                                                    <div class="d-flex justify-content-between small mb-1">
                                                        <strong>{{ $activity->alumni->user->name ?? '—' }}</strong>
                                                        <span class="text-muted">{{ $activity->screened_at?->diffForHumans() }}</span>
                                                    </div>
                                                    <p class="small text-muted mb-0">
                                                        {{ ucfirst($activity->status) }} — {{ $activity->office->title ?? '' }}
                                                    </p>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    if (typeof feather !== 'undefined') feather.replace();
</script>
@endpush
@endsection
