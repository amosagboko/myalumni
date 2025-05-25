@extends('layouts.elcom')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Manage Elections</h4>
                    <a href="{{ route('elcom.elections.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create New Election
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Accreditation Period</th>
                                    <th>Voting Period</th>
                                    <th>Total Offices</th>
                                    <th>Total Candidates</th>
                                    <th>Total Accredited</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($elections as $election)
                                    <tr>
                                        <td>{{ $election->title }}</td>
                                        <td>
                                            <span class="badge bg-{{ $election->status === 'draft' ? 'secondary' : 
                                                ($election->status === 'accreditation' ? 'info' : 
                                                ($election->status === 'voting' ? 'primary' : 
                                                ($election->status === 'completed' ? 'success' : 'danger'))) }}">
                                                {{ ucfirst($election->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $election->accreditation_start->format('M d, Y H:i') }} -
                                            {{ $election->accreditation_end->format('M d, Y H:i') }}
                                        </td>
                                        <td>
                                            {{ $election->voting_start->format('M d, Y H:i') }} -
                                            {{ $election->voting_end->format('M d, Y H:i') }}
                                        </td>
                                        <td>{{ $election->offices->count() }}</td>
                                        <td>{{ $election->candidates->count() }}</td>
                                        <td>{{ $election->getTotalAccreditedVoters() }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('elcom.elections.show', $election) }}" 
                                                    class="btn btn-sm btn-info" 
                                                    title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($election->status === 'draft')
                                                    <a href="{{ route('elcom.elections.edit', $election) }}" 
                                                        class="btn btn-sm btn-warning" 
                                                        title="Edit Election">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif

                                                @if($election->canStartAccreditation())
                                                    <form action="{{ route('elcom.elections.start-accreditation', $election) }}" 
                                                        method="POST" 
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" 
                                                            class="btn btn-sm btn-success" 
                                                            title="Start Accreditation"
                                                            onclick="return confirm('Are you sure you want to start the accreditation period?')">
                                                            <i class="fas fa-user-check"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($election->canStartVoting())
                                                    <form action="{{ route('elcom.elections.start-voting', $election) }}" 
                                                        method="POST" 
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" 
                                                            class="btn btn-sm btn-primary" 
                                                            title="Start Voting"
                                                            onclick="return confirm('Are you sure you want to start the voting period?')">
                                                            <i class="fas fa-vote-yea"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($election->canEndVoting())
                                                    <form action="{{ route('elcom.elections.end-voting', $election) }}" 
                                                        method="POST" 
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="End Voting"
                                                            onclick="return confirm('Are you sure you want to end the voting period? This will declare the results.')">
                                                            <i class="fas fa-flag-checkered"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($election->status === 'voting')
                                                    <a href="{{ route('elcom.elections.real-time-results', $election) }}" 
                                                        class="btn btn-sm btn-info" 
                                                        title="View Real-time Results">
                                                        <i class="fas fa-chart-line"></i>
                                                    </a>
                                                    <a href="{{ route('elcom.elections.basic-results', $election) }}" 
                                                        class="btn btn-sm btn-primary" 
                                                        title="View Basic Results">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                @endif

                                                @if($election->status === 'completed')
                                                    <a href="{{ route('elcom.elections.basic-results', $election) }}" 
                                                        class="btn btn-sm btn-primary" 
                                                        title="View Basic Results">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                    <a href="{{ route('elcom.elections.print-certificates', $election) }}" 
                                                        class="btn btn-sm btn-success" 
                                                        title="Print Certificates">
                                                        <i class="fas fa-certificate"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No elections found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $elections->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-group .btn {
        margin: 0 2px;
    }
</style>
@endpush 