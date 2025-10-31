<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Clearance Audit</h6>
                    <button class="btn btn-sm btn-success" wire:click="export">Export</button>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="row g-2">
                            <div class="col-md-2">
                                <select class="form-select" wire:model="division">
                                    <option value="">All Divisions</option>
                                    <option value="student_affairs">Student Affairs</option>
                                    <option value="academic_affairs">Academic Affairs</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Alumni name" wire:model.debounce.500ms="alumniName">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Actor name" wire:model.debounce.500ms="actorName">
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" wire:model="dateFrom">
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" wire:model="dateTo">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>When</th>
                                <th>Alumni</th>
                                <th>Matric</th>
                                <th>Division</th>
                                <th>Old → New</th>
                                <th>Actor (role)</th>
                                <th>Reason</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $log->alumni_name }}</td>
                                    <td>{{ $log->matric_number }}</td>
                                    <td>{{ $log->division === 'student_affairs' ? 'Student Affairs' : 'Academic Affairs' }}</td>
                                    <td>{{ $log->old_value ? '✔' : '✖' }} → {{ $log->new_value ? '✔' : '✖' }}</td>
                                    <td>{{ $log->actor_name }} ({{ $log->actor_role }})</td>
                                    <td>{{ $log->reason ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No audit logs found.</td>
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
