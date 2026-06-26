<x-alumniadmin-dashboard title="Fee Templates | FuLafia Alumni">
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Fee Templates</h5>
                                <a href="{{ route('admin.fee-templates.create') }}" class="btn btn-primary btn-sm">
                                    <i data-feather="plus" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                    Add New Template
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

                                <!-- Filters -->
                                <div class="mb-4 p-3 bg-light rounded">
                                    <form method="GET" class="row g-3">
                                        <div class="col-md-3">
                                            <label for="fee_type" class="form-label">Fee Type</label>
                                            <select name="fee_type" id="fee_type" class="form-select">
                                                <option value="">All Types</option>
                                                @foreach($feeTypes as $feeType)
                                                    <option value="{{ $feeType->id }}" {{ request('fee_type') == $feeType->id ? 'selected' : '' }}>
                                                        {{ $feeType->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="graduation_year" class="form-label">Graduation Year</label>
                                            <select name="graduation_year" id="graduation_year" class="form-select">
                                                <option value="">All Years</option>
                                                @for($year = date('Y') + 1; $year >= 2020; $year--)
                                                    <option value="{{ $year }}" {{ request('graduation_year') == $year ? 'selected' : '' }}>
                                                        {{ $year }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="category" class="form-label">Category</label>
                                            <select name="category" id="category" class="form-select">
                                                <option value="">All Categories</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="fee_purpose" class="form-label">Purpose</label>
                                            <select name="fee_purpose" id="fee_purpose" class="form-select">
                                                <option value="">All purposes</option>
                                                <option value="onboarding" {{ request('fee_purpose') === 'onboarding' ? 'selected' : '' }}>Onboarding</option>
                                                <option value="annual_renewal" {{ request('fee_purpose') === 'annual_renewal' ? 'selected' : '' }}>Annual renewal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-outline-primary me-2">
                                                <i data-feather="search" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                                Filter
                                            </button>
                                            <a href="{{ route('admin.fee-templates.index') }}" class="btn btn-outline-secondary">
                                                <i data-feather="x" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                                Clear
                                            </a>
                                        </div>
                                    </form>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fee Type</th>
                                                <th>Purpose</th>
                                                <th>Year</th>
                                                <th>Category</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Validity</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($feeTemplates as $template)
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold">{{ $template->feeType->name }}</div>
                                                        <small class="text-muted">{{ $template->feeType->code }}</small>
                                                    </td>
                                                    <td>
                                                        @if($template->fee_purpose === 'onboarding')
                                                            <span class="badge bg-info text-dark">Onboarding</span>
                                                        @elseif($template->fee_purpose === 'annual_renewal')
                                                            <span class="badge bg-secondary">Annual</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if((int) $template->graduation_year === 0)
                                                            <span class="badge bg-info text-dark">All years</span>
                                                        @else
                                                            {{ $template->graduation_year }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($template->category)
                                                            <span class="badge bg-primary">{{ $template->category->name }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="fw-bold">₦{{ number_format($template->amount, 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $template->is_active ? 'success' : 'danger' }}">
                                                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div>{{ \Carbon\Carbon::parse($template->valid_from)->format('M d, Y') }}</div>
                                                        @if($template->valid_until)
                                                            <small class="text-muted">to {{ \Carbon\Carbon::parse($template->valid_until)->format('M d, Y') }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('admin.fee-templates.edit', $template) }}" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i data-feather="edit-2" class="btn-round-md" style="width: 14px; height: 14px;"></i>
                                                            </a>
                                                            @if($template->is_active)
                                                                <form action="{{ route('admin.fee-templates.deactivate', $template) }}" 
                                                                      method="POST" 
                                                                      class="d-inline"
                                                                      onsubmit="return confirm('Deactivate this template?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                                                        <i data-feather="pause" class="btn-round-md" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <form action="{{ route('admin.fee-templates.activate', $template) }}" 
                                                                      method="POST" 
                                                                      class="d-inline"
                                                                      onsubmit="return confirm('Activate this template?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                                        <i data-feather="play" class="btn-round-md" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            @if($template->transactions->count() == 0)
                                                                <form action="{{ route('admin.fee-templates.destroy', $template) }}" 
                                                                      method="POST" 
                                                                      class="d-inline"
                                                                      onsubmit="return confirm('Delete this template? This action cannot be undone.');">
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
                                                    <td colspan="8" class="text-center">No fee templates found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4">
                                    {{ $feeTemplates->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 