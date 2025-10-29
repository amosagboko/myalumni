<div>
    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="mb-4 p-3 bg-light rounded">
        <div class="row g-3">
            <div class="col-md-2">
                <label for="search" class="form-label">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" id="search" 
                       placeholder="Name, Matric, Email" 
                       class="form-control">
            </div>
            <div class="col-md-2">
                <label for="faculty" class="form-label">Faculty</label>
                <select wire:model.live="faculty" id="faculty" class="form-select">
                    <option value="">All Faculties</option>
                    @foreach($faculties as $facultyOption)
                        <option value="{{ $facultyOption }}">{{ $facultyOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="graduation_year" class="form-label">Graduation Year</label>
                <select wire:model.live="graduationYear" id="graduation_year" class="form-select">
                    <option value="">All Years</option>
                    @foreach($graduationYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="category" class="form-label">Current Category</label>
                <select wire:model.live="category" id="category" class="form-select">
                    <option value="">All Categories</option>
                    <option value="unassigned">Unassigned</option>
                    @foreach($categories as $categoryOption)
                        <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button wire:click="clearFilters" class="btn btn-outline-secondary">
                    <i data-feather="x" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="mb-4 p-3 bg-primary bg-opacity-10 rounded">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="form-check">
                    <input type="checkbox" id="select-all" class="form-check-input" 
                           wire:click="toggleSelectAll">
                    <label class="form-check-label" for="select-all">
                        Select All (Page)
                    </label>
                </div>
                <select wire:model="bulkCategoryId" class="form-select" style="width: auto;">
                    <option value="">Select Category</option>
                    @foreach($categories as $categoryOption)
                        <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                    @endforeach
                </select>
                <button type="button" wire:click="bulkAssign" class="btn btn-primary btn-sm">
                    <i data-feather="users" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                    Bulk Assign
                </button>
            </div>
            <div class="text-muted">
                <span>{{ count($selectedAlumni) }}</span> alumni selected
            </div>
        </div>
    </div>

    <!-- Alumni Table -->
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
                    <th>Graduation Year</th>
                    <th style="width: 120px;">Current Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alumni as $alumnus)
                    <tr>
                        <td>
                            <input type="checkbox" class="alumni-checkbox form-check-input" 
                                   value="{{ $alumnus->id }}"
                                   wire:model="selectedAlumni"
                                   wire:key="checkbox-{{ $alumnus->id }}">
                        </td>
                        <td>
                            <div class="fw-bold">{{ $alumnus->user->name ?? 'N/A' }}</div>
                        </td>
                        <td>{{ $alumnus->matric_number ?? 'N/A' }}</td>
                        <td>{{ $alumnus->faculty ?? 'N/A' }}</td>
                        <td>{{ $alumnus->year_of_graduation ?? 'N/A' }}</td>
                        <td class="text-center" style="width: 120px;">
                            @if($alumnus->category)
                                <span class="badge bg-primary" style="font-size: 0.75rem;">{{ $alumnus->category->name }}</span>
                            @else
                                <span class="badge bg-secondary" style="font-size: 0.75rem;">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            <select class="category-select form-select form-select-sm" 
                                    wire:change="assignCategory({{ $alumnus->id }}, $event.target.value)"
                                    wire:key="select-{{ $alumnus->id }}-{{ $alumnus->category_id }}">
                                <option value="">Select Category</option>
                                @foreach($categories as $categoryOption)
                                    <option value="{{ $categoryOption->id }}" {{ $alumnus->category_id == $categoryOption->id ? 'selected' : '' }}>
                                        {{ $categoryOption->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No alumni found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $alumni->links() }}
    </div>

    <!-- Loading Indicator -->
    <div wire:loading wire:target="assignCategory,bulkAssign" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(0,0,0,0.3); z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

