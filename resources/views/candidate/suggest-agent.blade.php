@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Suggest Agent - {{ $election->title }}</h5>
                    <a href="{{ route('alumni.elections.expression-of-interest.status') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-2"></i>Back to Expression of Interest
                    </a>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted">Candidate Details</h6>
                        <p class="mb-1"><strong>Name:</strong> {{ $candidate->alumni->user->name }}</p>
                        <p class="mb-1"><strong>Office:</strong> {{ $candidate->office->title }}</p>
                        <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge bg-{{ $candidate->status === 'approved' ? 'success' : ($candidate->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($candidate->status) }}
                            </span>
                        </p>
                        @if($candidate->suggested_agent_id)
                            <p class="mb-1">
                                <strong>Suggested Agent:</strong> 
                                {{ $candidate->suggestedAgent->user->name }}
                                <span class="badge bg-{{ $candidate->agent_status === 'approved' ? 'success' : ($candidate->agent_status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($candidate->agent_status ?? 'Pending') }}
                                </span>
                            </p>
                        @endif
                    </div>

                    @if($candidate->agent_status === 'pending')
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            You have already suggested an agent. Waiting for ELCOM approval.
                            <form action="{{ route('candidate.elections.candidates.cancel-suggestion', [$election, $candidate]) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to cancel your agent suggestion?');">
                                @csrf
                                <button type="submit" class="btn btn-link text-danger p-0 ms-2">
                                    Cancel Suggestion
                                </button>
                            </form>
                        </div>
                    @elseif($candidate->agent_status === 'approved')
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Your agent suggestion has been approved by ELCOM.
                        </div>
                    @elseif($candidate->agent_status === 'rejected')
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle me-2"></i>
                            Your agent suggestion was rejected by ELCOM.
                            @if($candidate->agent_rejection_reason)
                                <p class="mb-0 mt-2"><strong>Reason:</strong> {{ $candidate->agent_rejection_reason }}</p>
                            @endif
                        </div>
                    @endif

                    @if(!$candidate->agent_status || $candidate->agent_status === 'rejected')
                        <form action="{{ route('candidate.elections.candidates.suggest-agent', [$election, $candidate]) }}" 
                              method="POST" 
                              id="suggestAgentForm">
                            @csrf
                            <div class="mb-3">
                                <label for="agent_email" class="form-label">Enter Alumni Email</label>
                                <div class="input-group">
                                    <input type="email" 
                                           name="agent_email" 
                                           id="agent_email" 
                                           class="form-control @error('agent_email') is-invalid @enderror"
                                           placeholder="Enter alumni email address"
                                           value="{{ old('agent_email') }}"
                                           required>
                                    <input type="hidden" 
                                           name="suggested_agent_id" 
                                           id="suggested_agent_id" 
                                           value="{{ old('suggested_agent_id') }}">
                                    <button type="submit" class="btn btn-primary">
                                        Suggest Agent
                                    </button>
                                </div>
                                @error('agent_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('suggested_agent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Enter the email address of the alumni you want to act as your agent. The alumni will be notified and must be approved by ELCOM.
                                </div>
                            </div>

                            @if(old('agent_email') && !old('suggested_agent_id'))
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    No alumni found with the email address: {{ old('agent_email') }}
                                </div>
                            @endif

                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Important Information</h6>
                                <ul class="mb-0">
                                    <li>Your agent suggestion must be approved by ELCOM.</li>
                                    <li>The suggested alumni will be notified of your request.</li>
                                    <li>You can cancel your suggestion while it's pending approval.</li>
                                    <li>Once approved, the alumni will be assigned the agent role for this election.</li>
                                    <li>Only alumni with valid email addresses (not system-generated) can be suggested as agents.</li>
                                </ul>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.input-group .form-control {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.input-group .btn {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.form-control.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
</style>
@endpush

@push('scripts')
<script>
// Remove all previous JavaScript code
</script>
@endpush

@endsection 