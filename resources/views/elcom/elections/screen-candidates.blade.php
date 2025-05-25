@extends('layouts.elcom')

@section('content')
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('elcom.elections.show', $election) }}" class="btn btn-outline-secondary btn-sm me-3">
                            <i class="fas fa-arrow-left"></i> Back to Election Details
                        </a>
                        <h3 class="card-title mb-0">Screen Candidates - {{ $office->title }}</h3>
                    </div>
                    <span class="badge bg-primary">{{ $election->title }}</span>
                </div>
                <div class="card-body">
                    @if($candidates->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No candidates have applied for this office yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Candidate</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
                                        <th>Submitted</th>
                                        <th>Documents</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($candidates as $candidate)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($candidate->passport)
                                                        <img src="{{ asset('storage/' . $candidate->passport) }}" 
                                                             alt="Passport" 
                                                             class="rounded-circle me-2"
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @endif
                                                    <div>
                                                        <div class="fw-medium">{{ $candidate->alumni->user->name }}</div>
                                                        <small class="text-muted">{{ $candidate->alumni->matriculation_number }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @switch($candidate->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge bg-success">Approved</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-danger">Rejected</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($candidate->has_paid_screening_fee)
                                                    <span class="badge bg-success">Paid</span>
                                                @else
                                                    <span class="badge bg-danger">Unpaid</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $candidate->created_at->format('M d, Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#documentsModal{{ $candidate->id }}">
                                                    <i class="bi bi-file-earmark-text me-1"></i>
                                                    View Documents
                                                </button>
                                            </td>
                                            <td>
                                                @if($candidate->status === 'pending' && $candidate->has_paid_screening_fee)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#approveModal{{ $candidate->id }}">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal{{ $candidate->id }}">
                                                        <i class="bi bi-x-circle me-1"></i>
                                                        Reject
                                                    </button>
                                                @elseif($candidate->status === 'approved')
                                                    <a href="{{ route('elcom.election-offices.candidates.assign-agent-form', [$election, $office, $candidate]) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-person-plus me-1"></i>
                                                        Manage Agent
                                                    </a>
                                                    @if($candidate->agent)
                                                        <small class="d-block text-muted mt-1">
                                                            Agent: {{ $candidate->agent->name }}
                                                        </small>
                                                    @endif
                                                @elseif($candidate->status === 'rejected')
                                                    <small class="text-muted">
                                                        Rejected: {{ Str::limit($candidate->rejection_reason, 50) }}
                                                    </small>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Documents Modal -->
                                        <div class="modal fade" id="documentsModal{{ $candidate->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Candidate Documents</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <h6>Passport Photo</h6>
                                                                @if($candidate->passport)
                                                                    <img src="{{ asset('storage/' . $candidate->passport) }}" 
                                                                         alt="Passport" 
                                                                         class="img-fluid rounded">
                                                                @else
                                                                    <p class="text-muted">No passport photo uploaded</p>
                                                                @endif
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Supporting Documents</h6>
                                                                @if($candidate->documents)
                                                                    @foreach(json_decode($candidate->documents) as $document)
                                                                        <a href="{{ asset('storage/' . $document) }}" 
                                                                           class="d-block mb-2" 
                                                                           target="_blank">
                                                                            <i class="bi bi-file-earmark-text me-1"></i>
                                                                            {{ basename($document) }}
                                                                        </a>
                                                                    @endforeach
                                                                @else
                                                                    <p class="text-muted">No supporting documents uploaded</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @if($candidate->manifesto)
                                                            <div class="mt-3">
                                                                <h6>Manifesto</h6>
                                                                <div class="bg-light p-3 rounded">
                                                                    {{ $candidate->manifesto }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal{{ $candidate->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('elcom.elections.screen-candidate', [$election, $office, $candidate]) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Approve Candidate</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to approve this candidate?</p>
                                                            <div class="mb-3">
                                                                <label for="remarks" class="form-label">Remarks (Optional)</label>
                                                                <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                                                            </div>
                                                            <input type="hidden" name="status" value="approved">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Approve Candidate</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $candidate->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('elcom.elections.screen-candidate', [$election, $office, $candidate]) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Candidate</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="remarks" class="form-label">Reason for Rejection</label>
                                                                <textarea class="form-control" id="remarks" name="remarks" rows="3" required></textarea>
                                                                <div class="form-text">Please provide a clear reason for rejection.</div>
                                                            </div>
                                                            <input type="hidden" name="status" value="rejected">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject Candidate</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 