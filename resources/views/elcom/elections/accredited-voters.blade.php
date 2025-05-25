@extends('layouts.elcom')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Accredited Voters - {{ $election->title }}</h5>
                    <a href="{{ route('elcom.elections.show', $election) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-2"></i>Back to Election
                    </a>
                </div>

                <div class="card-body">
                    @php
                        $totalAccredited = $election->getTotalAccreditedVoters();
                        $totalVoted = $election->getTotalVotes();
                        $totalNotVoted = $totalAccredited - $totalVoted;
                        $voterTurnout = ($totalAccredited > 0) ? ($totalVoted / $totalAccredited) * 100 : 0;
                    @endphp

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stats-card p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">Total Accredited</h6>
                                <h3 class="mb-0">{{ $totalAccredited }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">Voted</h6>
                                <h3 class="mb-0">{{ $totalVoted }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">Not Voted</h6>
                                <h3 class="mb-0">{{ $totalNotVoted }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">Voter Turnout</h6>
                                <h3 class="mb-0">{{ number_format($voterTurnout, 1) }}%</h3>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Matriculation ID</th>
                                    <th>Qualification</th>
                                    <th>Year of Graduation</th>
                                    <th>Accredited At</th>
                                    <th>Voting Status</th>
                                    <th>Voted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accreditedVoters as $voter)
                                    <tr>
                                        <td>{{ $voter->alumni->user->name }}</td>
                                        <td>{{ $voter->alumni->matriculation_id }}</td>
                                        <td>{{ $voter->alumni->qualification_type }} - {{ $voter->alumni->qualification_details }}</td>
                                        <td>{{ $voter->alumni->year_of_graduation }}</td>
                                        <td>{{ $voter->accredited_at->format('M d, Y h:i A') }}</td>
                                        <td>
                                            @if($voter->has_voted)
                                                <span class="badge bg-success">Voted</span>
                                            @else
                                                <span class="badge bg-warning">Not Voted</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $voter->voted_at ? $voter->voted_at->format('M d, Y h:i A') : 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No accredited voters found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $accreditedVoters->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 