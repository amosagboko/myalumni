@extends('layouts.alumni')

@section('content')
<div class="container-fluid mt-3 mt-md-5 pt-3 pt-md-7 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm border-0 rounded-4">
                <!-- Header -->
                <div class="card-header bg-white border-bottom-0 py-3 py-md-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h4 h-md-3 fw-bold text-primary mb-0">Expression of Interest</h1>
                            <p class="text-muted small mb-0">{{ $expressionOfInterest->election->title }}</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @switch($expressionOfInterest->status)
                                @case('pending')
                                    <div class="status-badge bg-warning-subtle text-warning px-2 py-1 rounded-pill small">
                                        <i class="bi bi-clock me-1"></i>
                                        <span class="d-none d-sm-inline">Pending Screening</span>
                                        <span class="d-inline d-sm-none">Pending</span>
                                    </div>
                                    @break
                                @case('approved')
                                    <div class="status-badge bg-success-subtle text-success px-2 py-1 rounded-pill small">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <span class="d-none d-sm-inline">Approved</span>
                                        <span class="d-inline d-sm-none">Approved</span>
                                    </div>
                                    @break
                                @case('rejected')
                                    <div class="status-badge bg-danger-subtle text-danger px-2 py-1 rounded-pill small">
                                        <i class="bi bi-x-circle me-1"></i>
                                        <span class="d-none d-sm-inline">Rejected</span>
                                        <span class="d-inline d-sm-none">Rejected</span>
                                    </div>
                                    @break
                            @endswitch
                            <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>
                                <span class="d-none d-sm-inline">Back</span>
                                <span class="d-inline d-sm-none">Back</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <!-- Mobile View - Cards -->
                    <div class="d-md-none">
                        <!-- Position Details -->
                        <div class="status-card mb-3 p-3 border rounded">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle-xs bg-primary-subtle text-primary me-2">
                                    <i class="bi bi-briefcase"></i>
                                </div>
                                <span class="fw-semibold small">Position Details</span>
                            </div>
                            <div>
                                <p class="mb-0 fw-medium small">{{ $expressionOfInterest->office->title }}</p>
                                <p class="text-muted x-small mb-0">{{ $expressionOfInterest->election->title }}</p>
                            </div>
                        </div>

                        <!-- Agent Status -->
                        <div class="status-card mb-3 p-3 border rounded">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle-xs bg-info-subtle text-info me-2">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <span class="fw-semibold small">Agent Status</span>
                            </div>
                            <div>
                                @if($expressionOfInterest->suggested_agent_id)
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="me-2 small">{{ optional($expressionOfInterest->suggestedAgent->user)->name ?? 'Unknown' }}</span>
                                        <span class="badge bg-{{ $expressionOfInterest->agent_status === 'approved' ? 'success' : ($expressionOfInterest->agent_status === 'rejected' ? 'danger' : 'warning') }} small">
                                            {{ ucfirst($expressionOfInterest->agent_status ?? 'Pending') }}
                                        </span>
                                    </div>
                                    @if($expressionOfInterest->agent_status === 'rejected' && $expressionOfInterest->agent_rejection_reason)
                                        <div class="alert alert-danger py-1 px-2 mb-2 x-small">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $expressionOfInterest->agent_rejection_reason }}
                                        </div>
                                    @endif
                                @else
                                    <p class="text-muted x-small mb-0">No agent has been suggested yet.</p>
                                @endif
                                <div class="d-flex gap-1 mt-2">
                                    <a href="{{ route('candidate.elections.candidates.suggest-agent-form', [$expressionOfInterest->election, $expressionOfInterest->id]) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-person-plus me-1"></i>
                                        {{ $expressionOfInterest->suggested_agent_id ? 'Change' : 'Suggest' }}
                                    </a>
                                    @if($expressionOfInterest->suggested_agent_id && in_array($expressionOfInterest->agent_status, ['pending', 'rejected']))
                                        <form action="{{ route('candidate.elections.candidates.cancel-suggestion', [$expressionOfInterest->election, $expressionOfInterest->id]) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to cancel your agent suggestion?');"
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-x-circle me-1"></i>Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Screening Details -->
                        @if($expressionOfInterest->screened_at)
                        <div class="status-card mb-3 p-3 border rounded">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle-xs bg-success-subtle text-success me-2">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                                <span class="fw-semibold small">Screening Details</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <p class="x-small text-muted mb-0">Screened by</p>
                                    <p class="mb-0 fw-medium small">{{ $expressionOfInterest->screener_name }}</p>
                                </div>
                                <div class="col-6">
                                    <p class="x-small text-muted mb-0">Screened at</p>
                                    <p class="mb-0 fw-medium small">{{ $expressionOfInterest->formatted_screened_at }}</p>
                                </div>
                                @if($expressionOfInterest->rejection_reason)
                                    <div class="col-12">
                                        <p class="x-small text-muted mb-0">Remarks</p>
                                        <p class="mb-0 text-danger x-small">{{ $expressionOfInterest->rejection_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Documents -->
                        <div class="status-card mb-3 p-3 border rounded">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle-xs bg-primary-subtle text-primary me-2">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <span class="fw-semibold small">Submitted Documents</span>
                            </div>
                            <div class="row g-2">
                                <!-- Passport Photo -->
                                <div class="col-6">
                                    <p class="x-small fw-medium text-muted mb-1">Passport Photograph</p>
                                    <div class="passport-photo-container">
                                        <img src="{{ Storage::url($expressionOfInterest->passport) }}" 
                                             alt="Passport" 
                                             class="img-fluid rounded-2"
                                             style="width: 100%; height: 80px; object-fit: cover;">
                                    </div>
                                </div>

                                <!-- Supporting Documents -->
                                <div class="col-6">
                                    <p class="x-small fw-medium text-muted mb-1">Supporting Documents</p>
                                    @if($expressionOfInterest->documents)
                                        <div class="d-flex flex-wrap gap-1" style="max-height: 80px; overflow-y: auto;">
                                            @foreach($expressionOfInterest->documents as $document)
                                                <a href="{{ Storage::url($document) }}" 
                                                   target="_blank"
                                                   class="btn btn-light btn-sm">
                                                    <i class="bi bi-file-earmark me-1"></i>
                                                    View
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted x-small mb-0">No supporting documents.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Manifesto -->
                        <div class="status-card mb-3 p-3 border rounded">
                            <p class="x-small fw-medium text-muted mb-1">Manifesto</p>
                            <div class="bg-light rounded-2 p-2" style="max-height: 100px; overflow-y: auto;">
                                <p class="x-small mb-0">{{ $expressionOfInterest->manifesto }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop View - Table -->
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <tbody>
                                    <!-- Position Details -->
                                    <tr>
                                        <th class="bg-light" style="width: 160px;">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle-xs bg-primary-subtle text-primary me-2">
                                                    <i class="bi bi-briefcase"></i>
                                                </div>
                                                <span class="fw-semibold">Position Details</span>
                                            </div>
                                        </th>
                                        <td>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="mb-0 fw-medium">{{ $expressionOfInterest->office->title }}</p>
                                                    <p class="text-muted small mb-0">{{ $expressionOfInterest->election->title }}</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Agent Status -->
                                    <tr>
                                        <th class="bg-light">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle-xs bg-info-subtle text-info me-2">
                                                    <i class="bi bi-person-badge"></i>
                                                </div>
                                                <span class="fw-semibold">Agent Status</span>
                                            </div>
                                        </th>
                                        <td>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    @if($expressionOfInterest->suggested_agent_id)
                                                        <div class="d-flex align-items-center mb-1">
                                                            <span class="me-2">{{ optional($expressionOfInterest->suggestedAgent->user)->name ?? 'Unknown' }}</span>
                                                            <span class="badge bg-{{ $expressionOfInterest->agent_status === 'approved' ? 'success' : ($expressionOfInterest->agent_status === 'rejected' ? 'danger' : 'warning') }}">
                                                                {{ ucfirst($expressionOfInterest->agent_status ?? 'Pending') }}
                                                            </span>
                                                        </div>
                                                        @if($expressionOfInterest->agent_status === 'rejected' && $expressionOfInterest->agent_rejection_reason)
                                                            <div class="alert alert-danger py-1 px-2 mb-0 small">
                                                                <i class="bi bi-exclamation-circle me-1"></i>{{ $expressionOfInterest->agent_rejection_reason }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        <p class="text-muted small mb-0">No agent has been suggested yet.</p>
                                                    @endif
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('candidate.elections.candidates.suggest-agent-form', [$expressionOfInterest->election, $expressionOfInterest->id]) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-person-plus me-1"></i>
                                                        {{ $expressionOfInterest->suggested_agent_id ? 'Change Agent' : 'Suggest Agent' }}
                                                    </a>
                                                    @if($expressionOfInterest->suggested_agent_id && in_array($expressionOfInterest->agent_status, ['pending', 'rejected']))
                                                        <form action="{{ route('candidate.elections.candidates.cancel-suggestion', [$expressionOfInterest->election, $expressionOfInterest->id]) }}" 
                                                              method="POST" 
                                                              onsubmit="return confirm('Are you sure you want to cancel your agent suggestion?');"
                                                              class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                <i class="bi bi-x-circle me-1"></i>Cancel
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Screening Details -->
                                    @if($expressionOfInterest->screened_at)
                                    <tr>
                                        <th class="bg-light">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle-xs bg-success-subtle text-success me-2">
                                                    <i class="bi bi-clipboard-check"></i>
                                                </div>
                                                <span class="fw-semibold">Screening Details</span>
                                            </div>
                                        </th>
                                        <td>
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <p class="small text-muted mb-0">Screened by</p>
                                                    <p class="mb-0 fw-medium">{{ $expressionOfInterest->screener_name }}</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="small text-muted mb-0">Screened at</p>
                                                    <p class="mb-0 fw-medium">{{ $expressionOfInterest->formatted_screened_at }}</p>
                                                </div>
                                                @if($expressionOfInterest->rejection_reason)
                                                    <div class="col-md-4">
                                                        <p class="small text-muted mb-0">Remarks</p>
                                                        <p class="mb-0 text-danger small">{{ $expressionOfInterest->rejection_reason }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endif

                                    <!-- Documents -->
                                    <tr>
                                        <th class="bg-light">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle-xs bg-primary-subtle text-primary me-2">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                                <span class="fw-semibold">Submitted Documents</span>
                                            </div>
                                        </th>
                                        <td>
                                            <div class="row g-2">
                                                <!-- Passport Photo -->
                                                <div class="col-md-3">
                                                    <div class="document-section h-100">
                                                        <p class="small fw-medium text-muted mb-1">Passport Photograph</p>
                                                        <div class="passport-photo-container">
                                                            <img src="{{ Storage::url($expressionOfInterest->passport) }}" 
                                                                 alt="Passport" 
                                                                 class="img-fluid rounded-2"
                                                                 style="width: 100%; height: 80px; object-fit: cover;">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Manifesto -->
                                                <div class="col-md-5">
                                                    <div class="document-section h-100">
                                                        <p class="small fw-medium text-muted mb-1">Manifesto</p>
                                                        <div class="bg-light rounded-2 p-2" style="height: 80px; overflow-y: auto;">
                                                            <p class="small mb-0">{{ $expressionOfInterest->manifesto }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Supporting Documents -->
                                                <div class="col-md-4">
                                                    <div class="document-section h-100">
                                                        <p class="small fw-medium text-muted mb-1">Supporting Documents</p>
                                                        @if($expressionOfInterest->documents)
                                                            <div class="d-flex flex-wrap gap-1" style="max-height: 80px; overflow-y: auto;">
                                                                @foreach($expressionOfInterest->documents as $document)
                                                                    <a href="{{ Storage::url($document) }}" 
                                                                       target="_blank"
                                                                       class="btn btn-light btn-sm">
                                                                        <i class="bi bi-file-earmark me-1"></i>
                                                                        View Document
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <p class="text-muted small mb-0">No supporting documents were submitted.</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-column flex-md-row justify-content-end gap-2 mt-3 pt-3 border-top">
                        @if($expressionOfInterest->status === 'approved')
                            <a href="{{ route('alumni.elections.published-candidates', ['election' => $expressionOfInterest->election, 'office' => $expressionOfInterest->office]) }}" 
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-people me-1"></i>
                                <span class="d-none d-sm-inline">View Published Candidates</span>
                                <span class="d-inline d-sm-none">View Candidates</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle-xs {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}

.status-badge {
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
}

.document-section {
    background: #fff;
    border-radius: 0.375rem;
    padding: 0.5rem;
}

.passport-photo-container {
    border-radius: 0.375rem;
    overflow: hidden;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.status-card {
    background: white;
    transition: all 0.3s ease;
}

.status-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container-fluid {
        margin-left: 0 !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
    
    .card-header {
        padding: 0.75rem;
    }
}

@media (max-width: 480px) {
    .container-fluid {
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }
    
    .card-body {
        padding: 0.5rem;
    }
    
    .btn {
        font-size: 0.75rem;
        padding: 0.35rem 0.5rem;
    }
}
</style>
@endsection 