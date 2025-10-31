<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            @if (session()->has('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Academic Affairs Clearance</h6>
                    <button wire:click="export" class="btn btn-sm btn-success">Export CSV</button>
                </div>
                <div class="card-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <input type="text" wire:model.debounce.500ms="search" placeholder="Search name or matric" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <select wire:model="faculty" class="form-select">
                                    <option value="">All Faculties</option>
                                    @foreach($faculties as $f)
                                        <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select wire:model="department" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select wire:model="year" class="form-select">
                                    <option value="">All Years</option>
                                    @foreach($years as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <select wire:model="perPage" class="form-select">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
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

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" class="form-check-input">
                                </th>
                                <th>Name</th>
                                <th>Matric</th>
                                <th>Faculty</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Onboarding</th>
                                <th>Payments</th>
                                <th>Academic Affairs</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($alumni as $a)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="alumni-checkbox form-check-input" 
                                               value="{{ $a->id }}"
                                               wire:model="selectedAlumni"
                                               wire:key="checkbox-{{ $a->id }}">
                                    </td>
                                    <td>{{ $a->user->name ?? 'N/A' }}</td>
                                    <td>{{ $a->matric_number ?? 'N/A' }}</td>
                                    <td>{{ $a->faculty ?? 'N/A' }}</td>
                                    <td>{{ $a->department ?? 'N/A' }}</td>
                                    <td>{{ $a->year_of_graduation ?? 'N/A' }}</td>
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
                                    <td>
                                        @if($a->academic_affairs_cleared)
                                            <span class="badge bg-success">✔</span>
                                        @else
                                            <span class="badge bg-danger">✖</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php($disabled = !($onboard && $paid))
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-success" @disabled($disabled)
                                                    wire:click="toggleClearance({{ $a->id }}, true)">Mark Cleared</button>
                                            <button class="btn btn-sm btn-outline-danger" @disabled($disabled)
                                                    wire:click="toggleClearance({{ $a->id }}, false, 'Reversal')">Unclear</button>
                                        </div>
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

                    <div class="mt-3">{{ $alumni->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
