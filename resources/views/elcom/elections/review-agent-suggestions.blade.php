@extends('layouts.elcom')

@section('content')
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Review Agent Suggestions - {{ $election->title }}</h5>
                    <a href="{{ route('elcom.elections.show', $election) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-2"></i>Back to Election
                    </a>
                </div>

                <div class="card-body">
                    @if($candidates->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No pending agent suggestions to review.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Candidate</th>
                                        <th>Office</th>
                                        <th>Suggested Agent</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($candidates as $candidate)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">{{ $candidate->alumni->user->name }}</h6>
                                                        <small class="text-muted">{{ $candidate->alumni->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $candidate->office->title }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">{{ $candidate->suggestedAgent->user->name }}</h6>
                                                        <small class="text-muted">{{ $candidate->suggestedAgent->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">Pending Review</span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" 
                                                            class="btn btn-success btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#approveModal{{ $candidate->id }}">
                                                        <i class="bi bi-check-circle me-1"></i>Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal{{ $candidate->id }}">
                                                        <i class="bi bi-x-circle me-1"></i>Reject
                                                    </button>
                                                </div>

                                                <!-- Approve Modal -->
                                                <div class="modal fade" id="approveModal{{ $candidate->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="{{ route('elcom.elections.candidates.approve-agent', [$election, $candidate]) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Approve Agent Suggestion</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to approve <strong>{{ $candidate->suggestedAgent->user->name }}</strong> as the agent for <strong>{{ $candidate->alumni->user->name }}</strong>?</p>
                                                                    <div class="mb-3">
                                                                        <label for="reason" class="form-label">Optional Comments</label>
                                                                        <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Add any comments about this approval..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-success">Approve Agent</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="rejectModal{{ $candidate->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="{{ route('elcom.elections.candidates.reject-agent', [$election, $candidate]) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Reject Agent Suggestion</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to reject <strong>{{ $candidate->suggestedAgent->user->name }}</strong> as the agent for <strong>{{ $candidate->alumni->user->name }}</strong>?</p>
                                                                    <div class="mb-3">
                                                                        <label for="reason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                                                        <textarea name="reason" 
                                                                                  id="reason" 
                                                                                  class="form-control @error('reason') is-invalid @enderror" 
                                                                                  rows="3" 
                                                                                  required
                                                                                  placeholder="Please provide a reason for rejecting this agent suggestion...">{{ old('reason') }}</textarea>
                                                                        @error('reason')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger">Reject Agent</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
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