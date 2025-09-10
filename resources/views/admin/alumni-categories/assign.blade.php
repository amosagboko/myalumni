<x-alumniadmin-dashboard title="Assign Alumni to Categories | FuLafia Alumni">
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Assign Alumni to Categories</h5>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.alumni-categories.export') }}" class="btn btn-success btn-sm">
                                        <i data-feather="download" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                        Export
                                    </a>
                                    <a href="{{ route('admin.alumni-categories.index') }}" class="btn btn-secondary btn-sm">
                                        <i data-feather="settings" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                        Manage Categories
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
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

                                @if(isset($error))
                                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                        {{ $error }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <!-- Search and Filters -->
                                <div class="mb-4 p-3 bg-light rounded">
                                    <form method="GET" class="row g-3">
                                        <div class="col-md-2">
                                            <label for="search" class="form-label">Search</label>
                                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                                   placeholder="Name, Matric, Email" 
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="faculty" class="form-label">Faculty</label>
                                            <select name="faculty" id="faculty" class="form-select">
                                                <option value="">All Faculties</option>
                                                @foreach($faculties as $faculty)
                                                    <option value="{{ $faculty }}" {{ request('faculty') == $faculty ? 'selected' : '' }}>
                                                        {{ $faculty }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="graduation_year" class="form-label">Graduation Year</label>
                                            <select name="graduation_year" id="graduation_year" class="form-select">
                                                <option value="">All Years</option>
                                                @foreach($graduationYears as $year)
                                                    <option value="{{ $year }}" {{ request('graduation_year') == $year ? 'selected' : '' }}>
                                                        {{ $year }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="category" class="form-label">Current Category</label>
                                            <select name="category" id="category" class="form-select">
                                                <option value="">All Categories</option>
                                                <option value="unassigned" {{ request('category') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-outline-primary me-2">
                                                <i data-feather="search" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                                Filter
                                            </button>
                                            <a href="{{ route('admin.alumni-categories.assign') }}" class="btn btn-outline-secondary">
                                                <i data-feather="x" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                                Clear
                                            </a>
                                        </div>
                                    </form>
                                </div>

                                <!-- Bulk Actions -->
                                <div class="mb-4 p-3 bg-primary bg-opacity-10 rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="form-check">
                                                <input type="checkbox" id="select-all" class="form-check-input">
                                                <label class="form-check-label" for="select-all">
                                                    Select All
                                                </label>
                                            </div>
                                            <select id="bulk-category" class="form-select" style="width: auto;">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" id="bulk-assign" class="btn btn-primary btn-sm">
                                                <i data-feather="users" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                                Bulk Assign
                                            </button>
                                        </div>
                                        <div class="text-muted">
                                            <span id="selected-count">0</span> alumni selected
                                        </div>
                                    </div>
                                </div>

                                <!-- Alumni Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="select-all-header" class="form-check-input">
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
                                                        <input type="checkbox" class="alumni-checkbox form-check-input" value="{{ $alumnus->id }}">
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
                                                                data-alumni-id="{{ $alumnus->id }}">
                                                            <option value="">Select Category</option>
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}" {{ $alumnus->category_id == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const selectAllHeader = document.getElementById('select-all-header');
        const alumniCheckboxes = document.querySelectorAll('.alumni-checkbox');
        const bulkCategorySelect = document.getElementById('bulk-category');
        const bulkAssignButton = document.getElementById('bulk-assign');
        const selectedCountSpan = document.getElementById('selected-count');
        const categorySelects = document.querySelectorAll('.category-select');

        // Select all functionality
        function updateSelectAll() {
            const checkedBoxes = document.querySelectorAll('.alumni-checkbox:checked');
            const totalBoxes = alumniCheckboxes.length;
            
            if (checkedBoxes.length === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (checkedBoxes.length === totalBoxes) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
            
            selectedCountSpan.textContent = checkedBoxes.length;
        }

        // Individual checkbox change
        alumniCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectAll);
        });

        // Select all checkbox change
        selectAllCheckbox.addEventListener('change', function() {
            alumniCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectAll();
        });

        // Header select all checkbox change
        selectAllHeader.addEventListener('change', function() {
            alumniCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            selectAllCheckbox.checked = this.checked;
            updateSelectAll();
        });

        // Category select change
        categorySelects.forEach(select => {
            select.addEventListener('change', function() {
                if (this.value) {
                    const alumniId = this.dataset.alumniId;
                    const categoryId = this.value;
                    
                    fetch('{{ route("admin.alumni-categories.assign-single") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            alumni_id: alumniId,
                            category_id: categoryId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while assigning category.');
                    });
                }
            });
        });

        // Bulk assign
        bulkAssignButton.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.alumni-checkbox:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedIds.length === 0) {
                alert('Please select at least one alumni.');
                return;
            }
            
            const categoryId = bulkCategorySelect.value;
            if (!categoryId) {
                alert('Please select a category.');
                return;
            }
            
            if (confirm(`Assign selected alumni to this category?`)) {
                fetch('{{ route("admin.alumni-categories.bulk-assign") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        alumni_ids: selectedIds,
                        category_id: categoryId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while bulk assigning categories.');
                });
            }
        });

        updateSelectAll();
    });
</script>