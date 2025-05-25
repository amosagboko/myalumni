<x-alumniadmin-dashboard>
    <div class="container mt-5 pt-7" style="margin-left: 200px;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0">Fee Management</h6>
                        @can('create fee templates')
                        <a href="{{ route('fee-templates.create') }}" class="btn btn-primary btn-sm">
                            Create Fee
                        </a>
                        @endcan
                    </div>
                    <div class="card-body p-3">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Year</th>
                                        <th>Fee Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Test Mode</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fees as $fee)
                                        <tr>
                                            <td>{{ $fee->category->name }}</td>
                                            <td>{{ $fee->alumniYear->year }}</td>
                                            <td>{{ $fee->fee_type }}</td>
                                            <td>₦{{ number_format($fee->amount, 2) }}</td>
                                            <td>
                                                <span class="badge {{ $fee->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $fee->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $fee->is_test_mode ? 'bg-warning' : 'bg-info' }}">
                                                    {{ $fee->is_test_mode ? 'Test Mode' : 'Live' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @can('view fee template details')
                                                    <a href="{{ route('fee-templates.show', $fee) }}" class="btn btn-outline-primary">
                                                        View
                                                    </a>
                                                    @endcan

                                                    @can('edit fee templates')
                                                    <a href="{{ route('fee-templates.edit', $fee) }}" class="btn btn-outline-secondary">
                                                        Edit
                                                    </a>
                                                    @endcan

                                                    @can('activate fee templates')
                                                    <button wire:click="toggleStatus({{ $fee->id }})" class="btn btn-outline-{{ $fee->is_active ? 'warning' : 'success' }}">
                                                        {{ $fee->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                    @endcan

                                                    @can('delete fee templates')
                                                    <button wire:click="delete({{ $fee->id }})" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this fee?')">
                                                        Delete
                                                    </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="feather-inbox mb-2" style="font-size: 2rem;"></i>
                                                    <p class="mb-0">No fees found.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $fees->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 