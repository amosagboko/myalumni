<x-alumniadmin-dashboard>
    <div class="container-fluid mt-5 pt-7" style="margin-left: 180px;">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Fee Types</h1>
                    <a href="{{ route('admin.fee-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Fee Type
                    </a>
                </div>

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

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($feeTypes as $feeType)
                                        <tr>
                                            <td>{{ $feeType->name }}</td>
                                            <td><code>{{ $feeType->code }}</code></td>
                                            <td>{{ Str::limit($feeType->description, 50) }}</td>
                                            <td>
                                                @if($feeType->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($feeType->is_system)
                                                    <span class="badge bg-info">System</span>
                                                @else
                                                    <span class="badge bg-secondary">Custom</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    @if(!$feeType->is_system)
                                                        <a href="{{ route('admin.fee-types.edit', $feeType) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                                        </a>
                                                        <form action="{{ route('admin.fee-types.toggle-status', $feeType) }}" 
                                                              method="POST" 
                                                              class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-warning"
                                                                    onclick="return confirm('Are you sure you want to {{ $feeType->is_active ? 'deactivate' : 'activate' }} this fee type?')">
                                                                <i data-feather="{{ $feeType->is_active ? 'x-circle' : 'check-circle' }}" style="width: 14px; height: 14px;"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.fee-types.destroy', $feeType) }}" 
                                                              method="POST" 
                                                              class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this fee type?')">
                                                                <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No fee types found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $feeTypes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 