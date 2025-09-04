@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Edit Fee Template</h2>
                <a href="{{ route('admin.fee-templates.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Back to List
                </a>
            </div>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.fee-templates.update', $feeTemplate) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fee Type -->
                    <div>
                        <label for="fee_type_id" class="block text-sm font-medium text-gray-700 mb-2">Fee Type *</label>
                        <select name="fee_type_id" id="fee_type_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Fee Type</option>
                            @foreach($feeTypes as $feeType)
                                <option value="{{ $feeType->id }}" {{ old('fee_type_id', $feeTemplate->fee_type_id) == $feeType->id ? 'selected' : '' }}>
                                    {{ $feeType->name }} ({{ $feeType->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('fee_type_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Graduation Year -->
                    <div>
                        <label for="graduation_year" class="block text-sm font-medium text-gray-700 mb-2">Graduation Year *</label>
                        <select name="graduation_year" id="graduation_year" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Year</option>
                            @for($year = date('Y') + 1; $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ old('graduation_year', $feeTemplate->graduation_year) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                        @error('graduation_year')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category (for 2025+) -->
                    <div id="category_section" class="{{ $feeTemplate->graduation_year >= 2025 ? '' : 'hidden' }}">
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Alumni Category {{ $feeTemplate->graduation_year >= 2025 ? '*' : '' }}</label>
                        <select name="category_id" id="category_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" {{ $feeTemplate->graduation_year >= 2025 ? 'required' : '' }}>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $feeTemplate->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Required for 2025+ graduates</p>
                        @error('category_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (₦) *</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount', $feeTemplate->amount) }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        @error('amount')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Valid From -->
                    <div>
                        <label for="valid_from" class="block text-sm font-medium text-gray-700 mb-2">Valid From *</label>
                        <input type="date" name="valid_from" id="valid_from" value="{{ old('valid_from', $feeTemplate->valid_from->format('Y-m-d')) }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        @error('valid_from')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Valid Until -->
                    <div>
                        <label for="valid_until" class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
                        <input type="date" name="valid_until" id="valid_until" value="{{ old('valid_until', $feeTemplate->valid_until ? $feeTemplate->valid_until->format('Y-m-d') : '') }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Leave empty for no expiration</p>
                        @error('valid_until')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" 
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $feeTemplate->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $feeTemplate->is_active) ? 'checked' : '' }} 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="window.history.back()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Update Fee Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const graduationYearSelect = document.getElementById('graduation_year');
    const categorySection = document.getElementById('category_section');
    const categorySelect = document.getElementById('category_id');

    function toggleCategorySection() {
        const selectedYear = parseInt(graduationYearSelect.value);
        if (selectedYear >= 2025) {
            categorySection.classList.remove('hidden');
            categorySelect.required = true;
        } else {
            categorySection.classList.add('hidden');
            categorySelect.required = false;
            categorySelect.value = '';
        }
    }

    graduationYearSelect.addEventListener('change', toggleCategorySection);
    toggleCategorySection(); // Run on page load
});
</script>
@endsection 