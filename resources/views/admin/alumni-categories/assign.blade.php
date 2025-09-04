<x-alumniadmin-dashboard>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Assign Alumni to Categories</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.alumni-categories.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-download mr-2"></i>Export
                        </a>
                        <a href="{{ route('admin.alumni-categories.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                            Manage Categories
                        </a>
                    </div>
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

                <!-- Search and Filters -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   placeholder="Name, Matric, Email" 
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
                                @for($year = date('Y'); $year >= 2020; $year--)
                                    <option value="{{ $year }}" {{ request('graduation_year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
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
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                                Filter
                            </button>
                            <a href="{{ route('admin.alumni-categories.assign') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Bulk Actions -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm font-medium text-gray-700">Select All</span>
                            </label>
                            <select id="bulk-category" class="border-gray-300 rounded-md shadow-sm">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="bulk-assign" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                                Bulk Assign
                            </button>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span id="selected-count">0</span> alumni selected
                        </div>
                    </div>
                </div>

                <!-- Alumni Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all-header" class="rounded border-gray-300 text-blue-600">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matric</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Graduation Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($alumni as $alumnus)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="alumni-checkbox rounded border-gray-300 text-blue-600" value="{{ $alumnus->id }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $alumnus->user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $alumnus->matric_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $alumnus->user->email }}
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
                                        <select class="category-select border-gray-300 rounded-md shadow-sm text-sm" 
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
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        No alumni found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $alumni->links() }}
                </div>
            </div>
        </div>
    </div>

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
                
                selectAllCheckbox.checked = checkedBoxes.length === totalBoxes && totalBoxes > 0;
                selectAllHeader.checked = selectAllCheckbox.checked;
                selectedCountSpan.textContent = checkedBoxes.length;
            }

            selectAllCheckbox.addEventListener('change', function() {
                alumniCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectAll();
            });

            selectAllHeader.addEventListener('change', function() {
                alumniCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectAll();
            });

            alumniCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectAll);
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

            // Bulk assignment
            bulkAssignButton.addEventListener('click', function() {
                const selectedAlumni = document.querySelectorAll('.alumni-checkbox:checked');
                const categoryId = bulkCategorySelect.value;
                
                if (selectedAlumni.length === 0) {
                    alert('Please select at least one alumni.');
                    return;
                }
                
                if (!categoryId) {
                    alert('Please select a category.');
                    return;
                }
                
                const alumniIds = Array.from(selectedAlumni).map(checkbox => checkbox.value);
                bulkAssignCategories(alumniIds, categoryId);
            });

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

            updateSelectAll();
        });
    </script>
</x-alumniadmin-dashboard> 