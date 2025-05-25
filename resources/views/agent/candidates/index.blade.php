@extends('layouts.app')

@section('content')
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">My Candidates</h3>
                </div>
                <div class="card-body">
                    @if($candidates->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            You haven't been assigned to any candidates yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Candidate</th>
                                        <th>Election</th>
                                        <th>Office</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
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
                                                <span class="badge bg-primary">{{ $candidate->election->title }}</span>
                                            </td>
                                            <td>{{ $candidate->office->title }}</td>
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
                                                <a href="{{ route('agent.candidates.show', [$candidate->election, $candidate]) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye me-1"></i>
                                                    View Details
                                                </a>
                                                @if(in_array($candidate->election->status, ['draft', 'accreditation']))
                                                    <a href="{{ route('agent.candidates.edit-documents', [$candidate->election, $candidate]) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil me-1"></i>
                                                        Edit Documents
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $candidates->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 