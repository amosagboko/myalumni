@extends('layouts.elcom')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0">Manage Elections</h4>
                    <div class="d-flex gap-2">
                        @if($canStartNewCycle)
                            <a href="{{ route('elcom.elections.new-cycle') }}" class="btn btn-success">
                                <i class="fas fa-redo me-1"></i> Start New Cycle
                            </a>
                            <a href="{{ route('elcom.elections.create') }}" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-1"></i> Create From Scratch
                            </a>
                        @else
                            <button class="btn btn-success" disabled title="Archive the completed election first">
                                <i class="fas fa-redo me-1"></i> Start New Cycle
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link {{ ($filter ?? 'active') === 'active' ? 'active' : '' }}"
                               href="{{ route('elcom.elections.index', ['filter' => 'active']) }}">Active</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($filter ?? '') === 'completed' ? 'active' : '' }}"
                               href="{{ route('elcom.elections.index', ['filter' => 'completed']) }}">Completed</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($filter ?? '') === 'archived' ? 'active' : '' }}"
                               href="{{ route('elcom.elections.index', ['filter' => 'archived']) }}">Archived</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($filter ?? '') === 'all' ? 'active' : '' }}"
                               href="{{ route('elcom.elections.index', ['filter' => 'all']) }}">All</a>
                        </li>
                    </ul>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Active</th>
                                    <th>Accreditation</th>
                                    <th>Voting</th>
                                    <th>Offices</th>
                                    <th>Ballots Cast</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($elections as $election)
                                    <tr>
                                        <td>{{ $election->election_year ?? '—' }}</td>
                                        <td>{{ $election->title }}</td>
                                        <td>
                                            @php
                                                $badge = match($election->status) {
                                                    'draft' => 'secondary',
                                                    'eoi' => 'warning',
                                                    'eoi_closed' => 'secondary',
                                                    'accreditation' => 'info',
                                                    'voting' => 'primary',
                                                    'incomplete' => 'warning',
                                                    'completed' => 'success',
                                                    'archived' => 'dark',
                                                    default => 'secondary',
                                                };
                                                $statusLabel = $election->status === 'incomplete' ? 'Incomplete' : ucfirst(str_replace('_', ' ', $election->status));
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ $statusLabel }}</span>
                                            @if($election->isByElection())
                                                <span class="badge bg-warning text-dark ms-1">By-Election</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($election->is_active)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="text-muted">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $election->accreditation_start?->format('M d, Y') }} –
                                            {{ $election->accreditation_end?->format('M d, Y') }}
                                        </td>
                                        <td>
                                            {{ $election->voting_start?->format('M d, Y') }} –
                                            {{ $election->voting_end?->format('M d, Y') }}
                                        </td>
                                        <td>{{ $election->offices->count() }}</td>
                                        <td>{{ number_format($election->getTotalVotes()) }}</td>
                                        <td>
                                            <a href="{{ route('elcom.elections.show', $election) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($election->status === 'completed')
                                                <form action="{{ route('elcom.elections.archive', $election) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-dark" title="Archive"
                                                        onclick="return confirm('Archive this election? It will become read-only.')">
                                                        <i class="fas fa-archive"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No elections found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $elections->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
