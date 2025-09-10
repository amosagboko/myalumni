<x-alumniadmin-dashboard title="Category Details | FuLafia Alumni">
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Category Details</h5>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.alumni-categories.edit', $alumniCategory) }}" class="btn btn-primary btn-sm">
                                        <i data-feather="edit-2" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                        Edit Category
                                    </a>
                                    <a href="{{ route('admin.alumni-categories.index') }}" class="btn btn-secondary btn-sm">
                                        <i data-feather="arrow-left" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                        Back to Categories
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Category Name</h6>
                                        <p class="fw-bold">{{ $alumniCategory->name }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Status</h6>
                                        <span class="badge bg-{{ $alumniCategory->is_active ? 'success' : 'danger' }}">
                                            {{ $alumniCategory->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Alumni Count</h6>
                                        <p class="fw-bold">{{ $alumniCategory->alumni_count }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Created</h6>
                                        <p>{{ $alumniCategory->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>

                                @if($alumniCategory->description)
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h6 class="text-muted">Description</h6>
                                            <p>{{ $alumniCategory->description }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($alumniCategory->alumni_count > 0)
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <h6 class="text-muted">Alumni in this Category</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Name</th>
                                                            <th>Matric Number</th>
                                                            <th>Faculty</th>
                                                            <th>Graduation Year</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($alumniCategory->alumni as $alumnus)
                                                            <tr>
                                                                <td>{{ $alumnus->user->name ?? 'N/A' }}</td>
                                                                <td>{{ $alumnus->matric_number ?? 'N/A' }}</td>
                                                                <td>{{ $alumnus->faculty ?? 'N/A' }}</td>
                                                                <td>{{ $alumnus->year_of_graduation ?? 'N/A' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>