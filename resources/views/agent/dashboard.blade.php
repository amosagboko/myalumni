@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-5 pt-7">
    <div class="row justify-content-end">
        <div class="col-lg-10" style="max-width: 1000px;">
            <div class="row justify-content-center">
                <!-- Statistics Cards -->
                <div class="col-lg-10 col-md-12 mb-4 mt-2">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-2">
                                    <h6 class="card-title small mb-1">Total Candidates</h6>
                                    <h4 class="mb-0">{{ $candidateStats['total'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body py-2">
                                    <h6 class="card-title small mb-1">Pending Screening</h6>
                                    <h4 class="mb-0">{{ $candidateStats['pending'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body py-2">
                                    <h6 class="card-title small mb-1">Approved</h6>
                                    <h4 class="mb-0">{{ $candidateStats['approved'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body py-2">
                                    <h6 class="card-title small mb-1">Unpaid Fees</h6>
                                    <h4 class="mb-0">{{ $candidateStats['unpaid'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Elections -->
                <div class="col-lg-7 col-md-12">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title mb-0">Active Elections</h6>
                        </div>
                        <div class="card-body p-2">
                            @if($activeElections->isEmpty())
                                <div class="alert alert-info py-2 mb-0 small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    No active elections found.
                                </div>
                            @else
                                @foreach($activeElections as $election)
                                    <div class="card mb-2">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="card-title mb-0 small">{{ $election->title }}</h6>
                                                <span class="badge bg-{{ $election->status === 'voting' ? 'success' : ($election->status === 'accreditation' ? 'warning' : 'info') }} small">
                                                    {{ ucfirst($election->status) }}
                                                </span>
                                            </div>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-sm small">
                                                    <thead>
                                                        <tr>
                                                            <th>Candidate</th>
                                                            <th>Office</th>
                                                            <th>Status</th>
                                                            <th>Payment</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($election->candidates as $candidate)
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        @if($candidate->passport)
                                                                            <img src="{{ asset('storage/' . $candidate->passport) }}" 
                                                                                 alt="Passport" 
                                                                                 class="rounded-circle me-2"
                                                                                 style="width: 24px; height: 24px; object-fit: cover;">
                                                                        @endif
                                                                        <div>
                                                                            <div class="fw-medium small">{{ $candidate->alumni->user->name }}</div>
                                                                            <small class="text-muted">{{ $candidate->alumni->matriculation_number }}</small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="small">{{ $candidate->office->title }}</td>
                                                                <td>
                                                                    <span class="badge bg-{{ $candidate->status === 'approved' ? 'success' : ($candidate->status === 'rejected' ? 'danger' : 'warning') }} small">
                                                                        {{ ucfirst($candidate->status) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    @if($candidate->has_paid_screening_fee)
                                                                        <span class="badge bg-success small">Paid</span>
                                                                    @else
                                                                        <span class="badge bg-danger small">Unpaid</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <a href="{{ route('agent.candidates.show', [$election, $candidate]) }}" 
                                                                       class="btn btn-sm btn-primary py-0 px-1">
                                                                        <i class="bi bi-eye small"></i>
                                                                    </a>
                                                                    @if(in_array($election->status, ['draft', 'accreditation']))
                                                                        <a href="{{ route('agent.candidates.edit-documents', [$election, $candidate]) }}" 
                                                                           class="btn btn-sm btn-outline-primary py-0 px-1">
                                                                            <i class="bi bi-pencil small"></i>
                                                                        </a>
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
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-lg-3 col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title mb-0">Recent Activities</h6>
                        </div>
                        <div class="card-body p-2">
                            @if($recentActivities->isEmpty())
                                <div class="alert alert-info py-2 mb-0 small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    No recent activities found.
                                </div>
                            @else
                                <div class="list-group list-group-flush">
                                    @foreach($recentActivities as $activity)
                                        <div class="list-group-item px-2 py-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 small">{{ $activity->alumni->user->name }}</h6>
                                                <small class="text-muted">{{ $activity->screened_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-1 small">
                                                <span class="badge bg-{{ $activity->status === 'approved' ? 'success' : 'danger' }} small">
                                                    {{ ucfirst($activity->status) }}
                                                </span>
                                                for {{ $activity->office->title }} in {{ $activity->election->title }}
                                            </p>
                                            @if($activity->remarks)
                                                <p class="mb-0 small text-muted">{{ Str::limit($activity->remarks, 100) }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 