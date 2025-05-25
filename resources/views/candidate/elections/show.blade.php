@extends('layouts.app')

@section('content')
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm me-3">
                            <i class="bi bi-arrow-left"></i> Back to Elections
                        </a>
                        <h3 class="card-title mb-0">Election Details</h3>
                    </div>
                    <span class="badge bg-primary">{{ $election->title }}</span>
                </div>

                <div class="card-body">
                    <!-- Election Details -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5 class="mb-3">Election Information</h5>
                            <p class="mb-2"><strong>Title:</strong> {{ $election->title }}</p>
                            <p class="mb-2"><strong>Description:</strong> {{ $election->description }}</p>
                            <p class="mb-2"><strong>Eligibility Criteria:</strong> {{ $election->eligibility_criteria }}</p>
                            <p class="mb-2">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $election->status === 'active' ? 'success' : ($election->status === 'ended' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($election->status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Candidate Status -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5 class="mb-3">Your Candidacy</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <p class="mb-2"><strong>Office:</strong> {{ $candidate->office->title }}</p>
                                    <p class="mb-2">
                                        <strong>Status:</strong>
                                        <span class="badge bg-{{ $candidate->status === 'approved' ? 'success' : ($candidate->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($candidate->status) }}
                                        </span>
                                    </p>
                                    @if($candidate->manifesto)
                                        <p class="mb-2"><strong>Manifesto:</strong></p>
                                        <div class="bg-white p-3 rounded">
                                            {{ $candidate->manifesto }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agent Information -->
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-3">Agent Information</h5>
                            @if($candidate->suggested_agent_id)
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <p class="mb-2">
                                            <strong>Suggested Agent:</strong> {{ $candidate->suggestedAgent->user->name }}
                                            <span class="badge bg-{{ $candidate->agent_status === 'approved' ? 'success' : ($candidate->agent_status === 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($candidate->agent_status ?? 'Pending') }}
                                            </span>
                                        </p>
                                        @if($candidate->agent_status === 'rejected' && $candidate->agent_rejection_reason)
                                            <p class="mb-0 text-danger">
                                                <strong>Rejection Reason:</strong> {{ $candidate->agent_rejection_reason }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(!$candidate->suggested_agent_id || $candidate->agent_status === 'rejected')
                                <div class="mt-3">
                                    <a href="{{ route('candidate.elections.candidates.suggest-agent-form', [$election, $candidate]) }}" 
                                       class="btn btn-primary">
                                        <i class="bi bi-person-plus me-2"></i>
                                        {{ $candidate->suggested_agent_id ? 'Change Agent' : 'Suggest Agent' }}
                                    </a>
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