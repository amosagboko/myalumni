@extends('layouts.alumni')

@section('content')
<div class="container-fluid bg-light min-vh-100 py-4 pt-7">
    <div class="row justify-content-end mt-5">
        <div class="col-lg-8 col-xl-9">
            <div class="card shadow-sm border-0 rounded-4">
                <!-- Header -->
                <div class="card-header bg-white border-bottom-0 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h5 fw-bold text-primary mb-0">Expression of Interest</h1>
                            <p class="text-muted x-small mb-0">{{ $expressionOfInterest->election->title }}</p>
                        </div>
                        @switch($expressionOfInterest->status)
                            @case('pending')
                                <div class="status-badge bg-warning-subtle text-warning px-2 py-1 rounded-pill">
                                    <i class="bi bi-clock me-1"></i>Pending Screening
                                </div>
                                @break
                            @case('approved')
                                <div class="status-badge bg-success-subtle text-success px-2 py-1 rounded-pill">
                                    <i class="bi bi-check-circle me-1"></i>Approved
                                </div>
                                @break
                            @case('rejected')
                                <div class="status-badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">
                                    <i class="bi bi-x-circle me-1"></i>Rejected
                                </div>
                                @break
                        @endswitch
                    </div>
                </div>

                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 small">
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
                                                <p class="text-muted x-small mb-0">{{ $expressionOfInterest->election->title }}</p>
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
                                                        <div class="alert alert-danger py-1 px-2 mb-0 x-small">
                                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $expressionOfInterest->agent_rejection_reason }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <p class="text-muted x-small mb-0">No agent has been suggested yet.</p>
                                                @endif
                                            </div>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('candidate.elections.candidates.suggest-agent-form', [$expressionOfInterest->election, $expressionOfInterest->id]) }}" 
                                                   class="btn btn-outline-primary btn-xs">
                                                    <i class="bi bi-person-plus me-1"></i>
                                                    {{ $expressionOfInterest->suggested_agent_id ? 'Change Agent' : 'Suggest Agent' }}
                                                </a>
                                                @if($expressionOfInterest->suggested_agent_id && in_array($expressionOfInterest->agent_status, ['pending', 'rejected']))
                                                    <form action="{{ route('candidate.elections.candidates.cancel-suggestion', [$expressionOfInterest->election, $expressionOfInterest->id]) }}" 
                                                          method="POST" 
                                                          onsubmit="return confirm('Are you sure you want to cancel your agent suggestion?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger btn-xs">
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
                                                <p class="x-small text-muted mb-0">Screened by</p>
                                                <p class="mb-0 fw-medium">{{ $expressionOfInterest->screener_name }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="x-small text-muted mb-0">Screened at</p>
                                                <p class="mb-0 fw-medium">{{ $expressionOfInterest->formatted_screened_at }}</p>
                                            </div>
                                            @if($expressionOfInterest->rejection_reason)
                                                <div class="col-md-4">
                                                    <p class="x-small text-muted mb-0">Remarks</p>
                                                    <p class="mb-0 text-danger x-small">{{ $expressionOfInterest->rejection_reason }}</p>
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
                                                    <p class="x-small fw-medium text-muted mb-1">Passport Photograph</p>
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
                                                    <p class="x-small fw-medium text-muted mb-1">Manifesto</p>
                                                    <div class="bg-light rounded-2 p-2" style="height: 80px; overflow-y: auto;">
                                                        <p class="x-small mb-0">{{ $expressionOfInterest->manifesto }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Supporting Documents -->
                                            <div class="col-md-4">
                                                <div class="document-section h-100">
                                                    <p class="x-small fw-medium text-muted mb-1">Supporting Documents</p>
                                                    @if($expressionOfInterest->documents)
                                                        <div class="d-flex flex-wrap gap-1" style="max-height: 80px; overflow-y: auto;">
                                                            @foreach($expressionOfInterest->documents as $document)
                                                                <a href="{{ Storage::url($document) }}" 
                                                                   target="_blank"
                                                                   class="btn btn-light btn-xs">
                                                                    <i class="bi bi-file-earmark me-1"></i>
                                                                    View Document
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-muted x-small mb-0">No supporting documents were submitted.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                        <a href="{{ route('alumni.elections') }}" 
                           class="btn btn-light btn-xs">
                            <i class="bi bi-arrow-left me-1"></i>Back to Elections
                        </a>
                        @if($expressionOfInterest->status === 'approved')
                            <a href="{{ route('alumni.elections.published-candidates', ['election' => $expressionOfInterest->election, 'office' => $expressionOfInterest->office]) }}" 
                               class="btn btn-primary btn-xs">
                                <i class="bi bi-people me-1"></i>View Published Candidates
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

/* Custom scrollbar */
.bg-light::-webkit-scrollbar,
.d-flex::-webkit-scrollbar {
    width: 3px;
}

.bg-light::-webkit-scrollbar-track,
.d-flex::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.bg-light::-webkit-scrollbar-thumb,
.d-flex::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.bg-light::-webkit-scrollbar-thumb:hover,
.d-flex::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Table styles */
.table > :not(caption) > * > * {
    padding: 0.5rem;
}

.table th {
    font-weight: 600;
    white-space: nowrap;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
}

/* Custom button size */
.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    line-height: 1.2;
    border-radius: 0.25rem;
}

/* Custom text size */
.x-small {
    font-size: 0.75rem;
}

/* Add margin to push content right and down on larger screens */
@media (min-width: 1200px) {
    .col-xl-9 {
        margin-right: 2rem;
        margin-top: 3rem;
    }
}

/* Add extra top margin for medium screens */
@media (min-width: 768px) and (max-width: 1199px) {
    .row.justify-content-end {
        margin-top: 4rem;
    }
}
</style>
@endsection 