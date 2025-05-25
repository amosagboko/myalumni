@extends('layouts.app')

@section('content')
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('agent.candidates.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                            <i class="bi bi-arrow-left"></i> Back to Candidates
                        </a>
                        <h3 class="card-title mb-0">Candidate Details</h3>
                    </div>
                    <span class="badge bg-primary">{{ $election->title }}</span>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Candidate Information -->
                        <div class="col-md-4">
                            <div class="card bg-light mb-4">
                                <div class="card-body text-center">
                                    @if($candidate->passport)
                                        <img src="{{ asset('storage/' . $candidate->passport) }}" 
                                             alt="Passport" 
                                             class="rounded-circle mb-3"
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto"
                                             style="width: 150px; height: 150px;">
                                            <i class="bi bi-person text-white" style="font-size: 4rem;"></i>
                                        </div>
                                    @endif
                                    <h4 class="mb-1">{{ $candidate->alumni->user->name }}</h4>
                                    <p class="text-muted mb-2">{{ $candidate->alumni->matriculation_number }}</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <span class="badge bg-{{ $candidate->status === 'approved' ? 'success' : ($candidate->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($candidate->status) }}
                                        </span>
                                        @if($candidate->has_paid_screening_fee)
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Office Information -->
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Office Details</h5>
                                    <p class="mb-1"><strong>Position:</strong> {{ $candidate->office->title }}</p>
                                    <p class="mb-1"><strong>Description:</strong></p>
                                    <p class="small text-muted">{{ $candidate->office->description }}</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            @if(in_array($election->status, ['draft', 'accreditation']))
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Actions</h5>
                                        <a href="{{ route('agent.candidates.edit-documents', [$election, $candidate]) }}" 
                                           class="btn btn-primary w-100 mb-2">
                                            <i class="bi bi-pencil me-1"></i>
                                            Edit Documents
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Documents and Manifesto -->
                        <div class="col-md-8">
                            <!-- Manifesto -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Manifesto</h5>
                                </div>
                                <div class="card-body">
                                    @if($candidate->manifesto)
                                        <div class="bg-light p-3 rounded">
                                            {{ $candidate->manifesto }}
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No manifesto has been submitted yet.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Documents -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Supporting Documents</h5>
                                </div>
                                <div class="card-body">
                                    @if($candidate->documents)
                                        <div class="list-group">
                                            @foreach(json_decode($candidate->documents) as $document)
                                                <a href="{{ asset('storage/' . $document) }}" 
                                                   class="list-group-item list-group-item-action d-flex align-items-center"
                                                   target="_blank">
                                                    <i class="bi bi-file-earmark-text me-2"></i>
                                                    {{ basename($document) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No supporting documents have been uploaded yet.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Screening Details -->
                            @if($candidate->screened_at)
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="card-title mb-0">Screening Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1">
                                            <strong>Screened by:</strong> {{ $candidate->screener->name ?? 'Unknown' }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Screened at:</strong> {{ $candidate->screened_at->format('F j, Y H:i:s') }}
                                        </p>
                                        @if($candidate->remarks)
                                            <div class="mt-3">
                                                <strong>Remarks:</strong>
                                                <p class="text-muted mb-0">{{ $candidate->remarks }}</p>
                                            </div>
                                        @endif
                                    </div>
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