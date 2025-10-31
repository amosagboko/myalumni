<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Academic Affairs Dashboard</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('academic-affairs.clearance') }}" class="btn btn-sm btn-primary">Go to Clearance</a>
                    <a href="{{ route('academic-affairs.audit') }}" class="btn btn-sm btn-outline-secondary">Clearance Audit</a>
                </div>
            </div>

            <!-- Quick Filters -->
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Quick Filters</h6></div>
                <div class="card-body">
                    <form action="{{ route('academic-affairs.clearance') }}" method="get" class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">By Faculty</label>
                            <select name="faculty" class="form-select">
                                <option value="">All Faculties</option>
                                @foreach($faculties as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">By Year</label>
                            <select name="year" class="form-select">
                                <option value="">All Years</option>
                                @foreach($years as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Go</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- KPIs -->
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm border-0 bg-light h-100">
                        <div class="card-body text-center">
                            <div class="small text-muted">Pending</div>
                            <div class="h5 mb-0">{{ number_format($kpis['pending']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm border-0 bg-light h-100">
                        <div class="card-body text-center">
                            <div class="small text-muted">Cleared Today</div>
                            <div class="h5 mb-0">{{ number_format($kpis['today']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm border-0 bg-light h-100">
                        <div class="card-body text-center">
                            <div class="small text-muted">Cleared This Week</div>
                            <div class="h5 mb-0">{{ number_format($kpis['week']) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm border-0 bg-light h-100">
                        <div class="card-body text-center">
                            <div class="small text-muted">Overall Cleared</div>
                            <div class="h5 mb-0">{{ number_format($kpis['overall']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Recent Activity</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                            <tr>
                                <th>When</th>
                                <th>Alumni</th>
                                <th>Matric</th>
                                <th>Old → New</th>
                                <th>Actor</th>
                                <th>Reason</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($recentActivity as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $log->alumni_name }}</td>
                                    <td>{{ $log->matric_number }}</td>
                                    <td>{{ $log->old_value ? '✔' : '✖' }} → {{ $log->new_value ? '✔' : '✖' }}</td>
                                    <td>{{ $log->actor_name }}</td>
                                    <td>{{ $log->reason ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3">No recent activity.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
