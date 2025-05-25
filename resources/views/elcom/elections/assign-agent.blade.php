@extends('layouts.elcom')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assign Agent - {{ $candidate->alumni->user->name }}</h5>
                    <a href="{{ route('elcom.election-offices.candidates.index', [$election, $office]) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-2"></i>Back to Candidates
                    </a>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted">Candidate Details</h6>
                        <p class="mb-1"><strong>Name:</strong> {{ $candidate->alumni->user->name }}</p>
                        <p class="mb-1"><strong>Office:</strong> {{ $office->title }}</p>
                        <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge bg-{{ $candidate->status === 'approved' ? 'success' : ($candidate->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($candidate->status) }}
                            </span>
                        </p>
                        @if($candidate->agent)
                            <p class="mb-1"><strong>Current Agent:</strong> {{ $candidate->agent->name }}</p>
                        @endif
                    </div>

                    @if($availableAgents->isEmpty())
                        <div class="alert alert-info">
                            No available agents found. All agents are already assigned to candidates in this election.
                        </div>
                    @else
                        <form action="{{ route('elcom.election-offices.candidates.assign-agent', [$election, $office, $candidate]) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="agent_id" class="form-label">Select Agent</label>
                                <select name="agent_id" id="agent_id" class="form-select @error('agent_id') is-invalid @enderror" required>
                                    <option value="">Select an agent...</option>
                                    @foreach($availableAgents as $agent)
                                        <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('agent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    Assign Agent
                                </button>
                                @if($candidate->agent)
                                    <a href="{{ route('elcom.elections.offices.candidates.remove-agent', [$election, $office, $candidate]) }}" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Are you sure you want to remove the current agent?')">
                                        Remove Current Agent
                                    </a>
                                @endif
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 