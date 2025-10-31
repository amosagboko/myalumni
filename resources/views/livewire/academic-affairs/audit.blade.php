<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-11 col-xl-10">
            @if (session()->has('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Academic Affairs Clearance Audit</h6>
                    <button wire:click="export" class="btn btn-sm btn-success">Export CSV</button>
                </div>
                <div class="card-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="Search alumni name" wire:model.debounce.500ms="alumniName">
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="Search actor name" wire:model.debounce.500ms="actorName">
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" placeholder="From date" wire:model="dateFrom">
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" placeholder="To date" wire:model="dateTo">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Who (User + Role)</th>
                                <th>Alumni</th>
                                <th>Matric</th>
                                <th>Old → New</th>
                                <th>Reason</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $log->actor_name }} <small class="text-muted">({{ $log->actor_role }})</small></td>
                                    <td>{{ $log->alumni_name }}</td>
                                    <td>{{ $log->matric_number }}</td>
                                    <td>
                                        <span class="badge {{ $log->old_value ? 'bg-success' : 'bg-danger' }}">{{ $log->old_value ? '✔' : '✖' }}</span>
                                        →
                                        <span class="badge {{ $log->new_value ? 'bg-success' : 'bg-danger' }}">{{ $log->new_value ? '✔' : '✖' }}</span>
                                    </td>
                                    <td>{{ $log->reason ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No audit logs found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $logs->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

