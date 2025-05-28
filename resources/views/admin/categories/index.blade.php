<x-alumniadmin-dashboard title="Alumni Categories | FuLafia Alumni">
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Alumni Categories</h5>
                                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                                    <i data-feather="plus" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                    Add New Category
                                </a>
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

                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Alumni Count</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($categories as $category)
                                                <tr>
                                                    <td>{{ $category->name }}</td>
                                                    <td>{{ Str::limit($category->description, 50) }}</td>
                                                    <td>{{ $category->alumni_count }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $category->is_active ? 'success' : 'danger' }}">
                                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('admin.categories.edit', $category) }}" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i data-feather="edit-2" class="btn-round-md" style="width: 14px; height: 14px;"></i>
                                                            </a>
                                                            @if($category->alumni_count === 0 && !$category->hasFees())
                                                                <form action="{{ route('admin.categories.destroy', $category) }}" 
                                                                      method="POST" 
                                                                      class="d-inline"
                                                                      onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                        <i data-feather="trash-2" class="btn-round-md" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">No categories found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4">
                                    {{ $categories->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 