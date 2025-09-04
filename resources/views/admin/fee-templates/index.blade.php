@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Fee Templates Management</h2>
                <a href="{{ route('admin.fee-templates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Create Fee Template
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

            <!-- Filters -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="fee_type" class="block text-sm font-medium text-gray-700 mb-1">Fee Type</label>
                        <select name="fee_type" id="fee_type" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">All Types</option>
                            @foreach($feeTypes as $feeType)
                                <option value="{{ $feeType->id }}" {{ request('fee_type') == $feeType->id ? 'selected' : '' }}>
                                    {{ $feeType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="graduation_year" class="block text-sm font-medium text-gray-700 mb-1">Graduation Year</label>
                        <select name="graduation_year" id="graduation_year" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">All Years</option>
                            @for($year = date('Y') + 1; $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ request('graduation_year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" id="category" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                            Filter
                        </button>
                        <a href="{{ route('admin.fee-templates.index') }}" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Fee Templates Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($feeTemplates as $template)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $template->feeType->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $template->feeType->code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $template->graduation_year }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($template->category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $template->category->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ₦{{ number_format($template->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($template->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>From: {{ $template->valid_from->format('M d, Y') }}</div>
                                    @if($template->valid_until)
                                        <div>To: {{ $template->valid_until->format('M d, Y') }}</div>
                                    @else
                                        <div class="text-gray-400">No expiration</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.fee-templates.edit', $template) }}" 
                                           class="text-blue-600 hover:text-blue-900">Edit</a>
                                        
                                        @if($template->is_active)
                                            <form action="{{ route('admin.fee-templates.deactivate', $template) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-yellow-600 hover:text-yellow-900" 
                                                        onclick="return confirm('Are you sure you want to deactivate this fee template?')">
                                                    Deactivate
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.fee-templates.activate', $template) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900">
                                                    Activate
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if(!$template->transactions()->exists())
                                            <form action="{{ route('admin.fee-templates.destroy', $template) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" 
                                                        onclick="return confirm('Are you sure you want to delete this fee template?')">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No fee templates found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($feeTemplates->hasPages())
                <div class="mt-6">
                    {{ $feeTemplates->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 