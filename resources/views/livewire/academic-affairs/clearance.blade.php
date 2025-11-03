<div class="middle-sidebar-bottom">
    <div class="middle-sidebar-left" style="margin-left: 280px; margin-top: 100px;">
        <div class="row">
            <div class="col-12">
            @if($message)
                <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                    {{ $message }}
                    <button type="button" class="btn-close" wire:click="clearMessage" aria-label="Close"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Academic Affairs Clearance</h6>
                    <button wire:click="export" class="btn btn-sm btn-success">Export CSV</button>
                </div>
                <div class="card-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <form method="GET" action="{{ route('academic-affairs.clearance') }}">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <input type="text" name="search" value="{{ request('search', $search) }}" placeholder="Search name or matric" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <select name="faculty" class="form-select">
                                        <option value="">All Faculties</option>
                                        @foreach($faculties as $f)
                                            <option value="{{ $f }}" {{ request('faculty', $faculty) == $f ? 'selected' : '' }}>{{ $f }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="department" class="form-select">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $d)
                                            <option value="{{ $d }}" {{ request('department', $department) == $d ? 'selected' : '' }}>{{ $d }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="year" class="form-select">
                                        <option value="">All Years</option>
                                        @foreach($years as $y)
                                            <option value="{{ $y }}" {{ request('year', $year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <select name="perPage" class="form-select">
                                        @foreach([10,20,50,100] as $n)
                                            <option value="{{ $n }}" {{ (int)request('perPage', $perPage) === $n ? 'selected' : '' }}>{{ $n }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 d-flex gap-2 mt-2">
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Apply Filters</button>
                                    <a href="{{ route('academic-affairs.clearance') }}" class="btn btn-outline-secondary btn-sm">Clear Filters</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="mb-3 p-3 bg-primary bg-opacity-10 rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check">
                                    <input type="checkbox" id="select-all-aa" class="form-check-input" 
                                           wire:click="toggleSelectAll">
                                    <label class="form-check-label" for="select-all-aa">
                                        Select All (Page)
                                    </label>
                                </div>
                                <button type="button" wire:click="bulkClear" class="btn btn-success btn-sm">
                                    <i data-feather="check-square" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                    Bulk Clear
                                </button>
                                <button type="button" wire:click="bulkUnclear" class="btn btn-danger btn-sm">
                                    <i data-feather="x-square" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                    Bulk Unclear
                                </button>
                            </div>
                            <div class="text-muted">
                                <span>{{ count($selectedAlumni) }}</span> alumni selected
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow-x:auto;">
                        <table class="table table-hover table-sm align-middle small">
                            <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input">
                                </th>
                                <th class="text-nowrap">Name</th>
                                <th class="text-nowrap">Matric</th>
                                <th class="text-nowrap">Faculty</th>
                                <th class="text-nowrap">Department</th>
                                <th class="text-nowrap">Year</th>
                                <th class="text-nowrap">Onboarding</th>
                                <th class="text-nowrap">Payments</th>
                                <th class="text-nowrap">AA</th>
                                <th class="text-nowrap">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($alumni as $a)
                                <tr wire:key="alumni-row-{{ $a->id }}-{{ $a->academic_affairs_cleared }}">
                                    <td>
                                        <input type="checkbox" class="alumni-checkbox form-check-input" 
                                               value="{{ $a->id }}"
                                               wire:model="selectedAlumni"
                                               wire:key="checkbox-{{ $a->id }}">
                                    </td>
                                    <td class="text-break">{{ $a->user->name ?? 'N/A' }}</td>
                                    <td class="text-break">{{ $a->matric_number ?? 'N/A' }}</td>
                                    <td class="text-break">{{ $a->faculty ?? 'N/A' }}</td>
                                    <td class="text-break">{{ $a->department ?? 'N/A' }}</td>
                                    <td class="text-break">{{ $a->year_of_graduation ?? 'N/A' }}</td>
                                    <td>
                                        @php($onboard = $a->biodata_completed ?? true)
                                        @if($onboard)
                                            <span class="badge bg-success">✔</span>
                                        @else
                                            <span class="badge bg-danger">✖</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php($paid = method_exists($a, 'hasCompletedRequiredPayments') ? $a->hasCompletedRequiredPayments() : true)
                                        @if($paid)
                                            <span class="badge bg-success">✔</span>
                                        @else
                                            <span class="badge bg-danger">✖</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap" wire:key="aa-badge-{{ $a->id }}-{{ $a->academic_affairs_cleared }}">
                                        @if($a->academic_affairs_cleared)
                                            <span class="badge bg-success">✔</span>
                                        @else
                                            <span class="badge bg-danger">✖</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @php($disabled = !($onboard && $paid))
                                        @if($disabled)
                                            <small class="text-muted">Complete onboarding & payments first</small>
                                        @else
                                            <div class="d-flex gap-2">
                                                <form method="POST" action="{{ route('academic-affairs.clearance.toggle', $a->id) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="value" value="1">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        Mark Cleared
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('academic-affairs.clearance.toggle', $a->id) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="value" value="0">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Unclear
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No records.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $alumni->appends(request()->query())->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
