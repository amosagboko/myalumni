<x-alumniadmin-dashboard>
    <div class="container mt-5 pt-7" style="margin-left: 200px;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0">Fee Management</h6>
                        @can('create fee templates')
                        <a href="{{ route('fee-templates.create') }}" class="btn btn-primary btn-sm">
                            <i class="feather-plus me-1"></i> Create Fee
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
                                        <th>Name</th>
                                        <th>Amount</th>
                                        <th>Validity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fees as $fee)
                                        <tr>
                                            <td>{{ $this->getCategoryName($fee) }}</td>
                                            <td>{{ $fee->graduation_year }}</td>
                                            <td>{{ $this->getFeeTypeName($fee) }}</td>
                                            <td>{{ $fee->name ?? 'N/A' }}</td>
                                            <td>{{ $this->getFormattedAmount($fee) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $this->getValidityStatus($fee) === 'Active' ? 'success' : ($this->getValidityStatus($fee) === 'Expired' ? 'danger' : 'warning') }}">
                                                    {{ $this->getValidityStatus($fee) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $fee->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $fee->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @can('view fee template details')
                                                    <a href="{{ route('fee-templates.show', $fee) }}" class="btn btn-outline-primary" title="View Details">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                    @endcan

                                                    @can('edit fee templates')
                                                    <a href="{{ route('fee-templates.edit', $fee) }}" class="btn btn-outline-secondary" title="Edit">
                                                        <i class="feather-edit-2"></i>
                                                    </a>
                                                    @endcan

                                                    @can('activate fee templates')
                                                    <button wire:click="toggleStatus({{ $fee->id }})" 
                                                            class="btn btn-outline-{{ $fee->is_active ? 'warning' : 'success' }}"
                                                            title="{{ $fee->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="feather-toggle-{{ $fee->is_active ? 'left' : 'right' }}"></i>
                                                    </button>
                                                    @endcan

                                                    @can('delete fee templates')
                                                    <button wire:click="delete({{ $fee->id }})" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this fee template?')"
                                                            title="Delete">
                                                        <i class="feather-trash-2"></i>
                                                    </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="feather-inbox mb-2" style="font-size: 2rem;"></i>
                                                    <p class="mb-0">No fee templates found.</p>
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