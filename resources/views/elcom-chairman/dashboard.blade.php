<x-layouts.elcom-chairman>
    <div class="container mt-5 pt-5" style="max-width: 900px;">
        <div class="row g-2">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-current d-flex justify-content-between align-items-center py-1 px-3">
                        <h6 class="mb-0 text-white fs-6">ELCOM Chairman Dashboard</h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white mb-2">
                                    <div class="card-body p-2">
                                        <h5 class="card-title fs-6 mb-1 text-white">Active Elections</h5>
                                        <p class="card-text display-6 mb-0 text-white">{{ $activeElections ?? 0 }}</p>
                                        <small class="text-white-50">In Progress</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white mb-2">
                                    <div class="card-body p-2">
                                        <h5 class="card-title fs-6 mb-1 text-white">Total EOI</h5>
                                        <p class="card-text display-6 mb-0 text-white">{{ $totalCandidates ?? 0 }}</p>
                                        <small class="text-white-50">All Elections</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white mb-2">
                                    <div class="card-body p-2">
                                        <h5 class="card-title fs-6 mb-1 text-white">Total Votes Cast</h5>
                                        <p class="card-text display-6 mb-0 text-white">{{ $totalVotes ?? 0 }}</p>
                                        <small class="text-white-50">Voter Participation</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white mb-2">
                                    <div class="card-body p-2">
                                        <h5 class="card-title fs-6 mb-1 text-white">Total Elections</h5>
                                        <p class="card-text display-6 mb-0 text-white">{{ $totalElections ?? 0 }}</p>
                                        <small class="text-white-50">All Time</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Statistics Row -->
                        <div class="row g-2 mt-1">
                            <div class="col-md-4">
                                <div class="card bg-secondary text-white mb-2">
                                    <div class="card-body p-2">
                                        <h5 class="card-title fs-6 mb-1 text-white">Completed Elections</h5>
                                        <p class="card-text display-6 mb-0 text-white">{{ $completedElections ?? 0 }}</p>
                                        <small class="text-white-50">Finished</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-dark text-white mb-2">
                                    <div class="card-body p-2">
                                        <h5 class="card-title fs-6 mb-1 text-white">Pending Elections</h5>
                                        <p class="card-text display-6 mb-0 text-white">{{ $pendingElections ?? 0 }}</p>
                                        <small class="text-white-50">Draft Status</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white mb-2">
                                    <div class="card-body p-2">
                                        <h5 class="card-title fs-6 mb-1 text-white">Accredited Voters</h5>
                                        <p class="card-text display-6 mb-0 text-white">{{ $totalAccreditedVoters ?? 0 }}</p>
                                        <small class="text-white-50">Total Registered</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-current d-flex justify-content-between align-items-center py-1 px-3">
                                        <h6 class="mb-0 text-white fs-6">Recent Elections</h6>
                                        <a href="{{ route('elcom-chairman.elections.index') }}" class="btn btn-sm btn-light py-0 px-2">View All</a>
                                    </div>
                                    <div class="card-body p-2">
                                        @if(isset($recentElections) && count($recentElections) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-hover table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="fs-6 py-1">Title</th>
                                                            <th class="fs-6 py-1">Status</th>
                                                            <th class="fs-6 py-1">Start Date</th>
                                                            <th class="fs-6 py-1">End Date</th>
                                                            <th class="fs-6 py-1">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($recentElections as $election)
                                                            <tr>
                                                                <td class="fs-6 py-1">{{ $election->title }}</td>
                                                                <td class="py-1">
                                                                    <span class="badge bg-{{ $election->status === 'active' ? 'success' : ($election->status === 'pending' ? 'warning' : 'secondary') }}">
                                                                        {{ ucfirst($election->status) }}
                                                                    </span>
                                                                </td>
                                                                <td class="fs-6 py-1">{{ $election->start_date ? $election->start_date->format('M d, Y') : 'Not set' }}</td>
                                                                <td class="fs-6 py-1">{{ $election->end_date ? $election->end_date->format('M d, Y') : 'Not set' }}</td>
                                                                <td class="py-1">
                                                                    <a href="{{ route('elcom-chairman.elections.show', $election) }}" class="btn btn-sm btn-primary py-0 px-2">View</a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-3">
                                                <i data-feather="calendar" class="font-xl text-muted mb-2" style="width: 32px; height: 32px;"></i>
                                                <h5 class="text-muted fs-6 mb-1">No Elections Found</h5>
                                                <p class="text-muted fs-6 mb-2">There are no recent elections to display.</p>
                                                <a href="{{ route('elcom-chairman.elections.create') }}" class="btn btn-primary btn-sm py-0 px-2">Create New Election</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.elcom-chairman> 