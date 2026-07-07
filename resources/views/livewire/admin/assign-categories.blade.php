<div>
    <div class="adt-panel">
        @if (session()->has('success'))
            <div class="adt-alert adt-alert-success mx-3 mt-3 mb-0" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="adt-alert adt-alert-error mx-3 mt-3 mb-0" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="adt-toolbar">
            <div class="adt-filters">
                <div class="adt-search">
                    <i data-feather="search" class="adt-search-icon"></i>
                    <input
                        type="text"
                        wire:model.defer="search"
                        id="search"
                        placeholder="Name, matric, or email"
                        class="form-control form-control-sm"
                    >
                </div>
                <select wire:model.defer="faculty" id="faculty" class="form-select form-select-sm adt-select">
                    <option value="">All faculties</option>
                    @foreach($faculties as $facultyOption)
                        <option value="{{ $facultyOption }}">{{ $facultyOption }}</option>
                    @endforeach
                </select>
                <select wire:model.defer="graduationYear" id="graduation_year" class="form-select form-select-sm adt-select adt-select-narrow">
                    <option value="">All years</option>
                    @foreach($graduationYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
                <select wire:model.defer="category" id="category" class="form-select form-select-sm adt-select">
                    <option value="">All categories</option>
                    <option value="unassigned">Unassigned</option>
                    @foreach($categories as $categoryOption)
                        <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                    @endforeach
                </select>
                <button wire:click="applyFilters" class="btn btn-sm ads-btn-primary">
                    <i data-feather="filter" style="width: 14px; height: 14px;"></i>
                    Filter
                </button>
                <button wire:click="clearFilters" class="btn btn-sm btn-outline-secondary">
                    Clear
                </button>
            </div>
        </div>

        <div class="px-3 py-3 border-top border-bottom bg-light-subtle">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="form-check mb-0">
                        <input type="checkbox" id="select-all" class="form-check-input" wire:click="toggleSelectAll">
                        <label class="form-check-label small" for="select-all">Select page</label>
                    </div>
                    <select wire:model="bulkCategoryId" class="form-select form-select-sm adt-select">
                        <option value="">Select category</option>
                        @foreach($categories as $categoryOption)
                            <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="bulkAssign" class="btn btn-sm ads-btn-primary">
                        <i data-feather="users" style="width: 14px; height: 14px;"></i>
                        Bulk assign
                    </button>
                </div>
                <div class="small text-muted">
                    {{ count($selectedAlumni) }} alumni selected
                </div>
            </div>
        </div>

        @if($alumni->count() > 0)
            <div class="adt-table-wrap">
                <table class="adt-table">
                    <thead>
                        <tr>
                            <th style="width: 44px;"></th>
                            <th>Name</th>
                            <th>Matric</th>
                            <th>Faculty</th>
                            <th>Graduation year</th>
                            <th>Current category</th>
                            <th style="min-width: 220px;">Assign category</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumni as $alumnus)
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        class="alumni-checkbox form-check-input"
                                        value="{{ $alumnus->id }}"
                                        wire:model="selectedAlumni"
                                        wire:key="checkbox-{{ $alumnus->id }}"
                                    >
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $alumnus->user->name ?? 'N/A' }}</div>
                                </td>
                                <td class="adt-muted">{{ $alumnus->matric_number ?? 'N/A' }}</td>
                                <td class="adt-muted">{{ $alumnus->faculty ?? 'N/A' }}</td>
                                <td class="adt-muted">{{ $alumnus->year_of_graduation ?? 'N/A' }}</td>
                                <td>
                                    @if($alumnus->category)
                                        <span class="adt-tag">{{ $alumnus->category->name }}</span>
                                    @else
                                        <span class="adt-tag">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <select
                                        class="form-select form-select-sm"
                                        wire:change="assignCategory({{ $alumnus->id }}, $event.target.value)"
                                        wire:key="select-{{ $alumnus->id }}-{{ $alumnus->category_id }}"
                                    >
                                        <option value="">Select category</option>
                                        @foreach($categories as $categoryOption)
                                            <option value="{{ $categoryOption->id }}" {{ $alumnus->category_id == $categoryOption->id ? 'selected' : '' }}>
                                                {{ $categoryOption->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($alumni->hasPages())
                <div class="adt-footer">
                    <span class="adt-footer-count">
                        {{ $alumni->firstItem() }}–{{ $alumni->lastItem() }} of {{ $alumni->total() }}
                    </span>
                    <div class="adt-pagination">
                        {{ $alumni->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        @else
            <div class="adt-empty">
                <div class="adt-empty-icon">
                    <i data-feather="users" style="width: 28px; height: 28px;"></i>
                </div>
                <h3 class="adt-empty-title">No alumni found</h3>
                <p class="adt-empty-text">Try adjusting your search or filters.</p>
            </div>
        @endif
    </div>

    <div wire:loading.delay wire:target="assignCategory,bulkAssign,applyFilters,clearFilters,toggleSelectAll" class="position-fixed bottom-0 end-0 m-3" style="z-index: 9999;">
        <div class="ads-alert mb-0 d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span>Processing...</span>
        </div>
    </div>
</div>

