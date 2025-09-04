@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Alumni Category Assignment</h2>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Back to Dashboard
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Search Filters -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               placeholder="Name, Matric Number, Email" 
                               class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="faculty" class="block text-sm font-medium text-gray-700 mb-1">Faculty</label>
                        <select name="faculty" id="faculty" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">All Faculties</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty }}" {{ request('faculty') == $faculty ? 'selected' : '' }}>
                                    {{ $faculty }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="graduation_year" class="block text-sm font-medium text-gray-700 mb-1">Graduation Year</label>
                        <select name="graduation_year" id="graduation_year" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">All Years</option>
                            @foreach($graduationYears as $year)
                                <option value="{{ $year }}" {{ request('graduation_year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Current Category</label>
                        <select name="category" id="category" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">All Categories</option>
                            <option value="unassigned" {{ request('category') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                            Search
                        </button>
                        <a href="{{ route('admin.alumni-categories.index') }}" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Bulk Actions -->
            <div class="mb-4 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ $alumni->total() }} alumni found</span>
                    <button id="selectAllBtn" class="text-blue-600 hover:text-blue-800 text-sm">Select All</button>
                    <button id="deselectAllBtn" class="text-gray-600 hover:text-gray-800 text-sm hidden">Deselect All</button>
                </div>
                <div class="flex items-center space-x-2">
                    <select id="bulkCategory" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button id="bulkAssignBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm" disabled>
                        Assign Selected
                    </button>
                </div>
            </div>

            <!-- Alumni Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alumni</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($alumni as $alumnus)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="alumni-checkbox rounded border-gray-300" value="{{ $alumnus->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ substr($alumnus->user->name, 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $alumnus->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $alumnus->matric_number }}</div>
                                            <div class="text-sm text-gray-500">{{ $alumnus->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $alumnus->faculty }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $alumnus->year_of_graduation }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($alumnus->category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $alumnus->category->name }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Unassigned
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <select class="category-select border-gray-300 rounded-md shadow-sm text-sm" 
                                                data-alumni-id="{{ $alumnus->id }}">
                                            <option value="">Change Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" 
                                                        {{ $alumnus->category_id == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No alumni found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($alumni->hasPages())
                <div class="mt-6">
                    {{ $alumni->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const alumniCheckboxes = document.querySelectorAll('.alumni-checkbox');
    const bulkCategorySelect = document.getElementById('bulkCategory');
    const bulkAssignBtn = document.getElementById('bulkAssignBtn');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const categorySelects = document.querySelectorAll('.category-select');

    // Select All functionality
    selectAllCheckbox.addEventListener('change', function() {
        alumniCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkAssignButton();
        toggleSelectButtons();
    });

    // Individual checkbox change
    alumniCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkAssignButton();
            toggleSelectButtons();
        });
    });

    // Select All/Deselect All buttons
    selectAllBtn.addEventListener('click', function() {
        alumniCheckboxes.forEach(checkbox => checkbox.checked = true);
        selectAllCheckbox.checked = true;
        updateBulkAssignButton();
        toggleSelectButtons();
    });

    deselectAllBtn.addEventListener('click', function() {
        alumniCheckboxes.forEach(checkbox => checkbox.checked = false);
        selectAllCheckbox.checked = false;
        updateBulkAssignButton();
        toggleSelectButtons();
    });

    // Bulk category assignment
    bulkAssignBtn.addEventListener('click', function() {
        const selectedAlumni = Array.from(alumniCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);
        
        const categoryId = bulkCategorySelect.value;
        
        if (selectedAlumni.length === 0) {
            alert('Please select at least one alumni.');
            return;
        }
        
        if (!categoryId) {
            alert('Please select a category.');
            return;
        }
        
        if (confirm(`Assign ${selectedAlumni.length} alumni to the selected category?`)) {
            bulkAssignCategories(selectedAlumni, categoryId);
        }
    });

    // Individual category assignment
    categorySelects.forEach(select => {
        select.addEventListener('change', function() {
            const alumniId = this.dataset.alumniId;
            const categoryId = this.value;
            
            if (categoryId) {
                assignCategory(alumniId, categoryId);
            }
        });
    });

    function updateSelectAllCheckbox() {
        const checkedCount = Array.from(alumniCheckboxes).filter(checkbox => checkbox.checked).length;
        selectAllCheckbox.checked = checkedCount === alumniCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < alumniCheckboxes.length;
    }

    function updateBulkAssignButton() {
        const selectedCount = Array.from(alumniCheckboxes).filter(checkbox => checkbox.checked).length;
        bulkAssignBtn.disabled = selectedCount === 0 || !bulkCategorySelect.value;
        bulkAssignBtn.textContent = `Assign ${selectedCount} Selected`;
    }

    function toggleSelectButtons() {
        const checkedCount = Array.from(alumniCheckboxes).filter(checkbox => checkbox.checked).length;
        selectAllBtn.classList.toggle('hidden', checkedCount === alumniCheckboxes.length);
        deselectAllBtn.classList.toggle('hidden', checkedCount === 0);
    }

    function assignCategory(alumniId, categoryId) {
        fetch(`/admin/alumni-categories/assign`, {
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
            alert('An error occurred while assigning the category.');
        });
    }

    function bulkAssignCategories(alumniIds, categoryId) {
        fetch(`/admin/alumni-categories/bulk-assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                alumni_ids: alumniIds,
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

    // Initialize
    updateBulkAssignButton();
    toggleSelectButtons();
});
</script>
@endsection 