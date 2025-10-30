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
                <div class="card-header bg-white">
                    <h6 class="mb-0">Student Affairs Clearance</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" wire:model.debounce.500ms="search" placeholder="Search name or matric" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <select wire:model="faculty" class="form-select">
                                    <option value="">All Faculties</option>
                                    @foreach($faculties as $f)
                                        <option value="{{ $f }}">{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select wire:model="year" class="form-select">
                                    <option value="">All Years</option>
                                    @foreach($years as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Matric</th>
                                <th>Faculty</th>
                                <th>Year</th>
                                <th>Onboarding</th>
                                <th>Payments</th>
                                <th>Student Affairs</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($alumni as $a)
                                <tr>
                                    <td>{{ $a->user->name ?? 'N/A' }}</td>
                                    <td>{{ $a->matric_number ?? 'N/A' }}</td>
                                    <td>{{ $a->faculty ?? 'N/A' }}</td>
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
                                        @if($a->student_affairs_cleared)
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
                                    <td colspan="8" class="text-center">No records.</td>
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
