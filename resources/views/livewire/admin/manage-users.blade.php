<div class="container pt-7" style="margin-left: 150px; margin-top: 50px;">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-current d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 text-white">Manage Users</h6>
                    @if($isAdmin)
                        <a href="{{ route('admin.users.create') }}" class="btn btn-success btn-sm">
                            <i class="feather-user-plus me-1"></i> Add User
                        </a>
                    @endif
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex gap-2">
                            <div class="input-group" style="width: 250px;">
                                <input type="text" wire:model.live="search" class="form-control form-control-sm" placeholder="Search users...">
                                <span class="input-group-text bg-current text-white">
                                    <i class="ti-search"></i>
                                </span>
                            </div>
                            <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width: 120px;">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="text-muted small">
                            Logged in as: {{ Auth::user()->name }} ({{ Auth::user()->roles->pluck('name')->implode(', ') }})
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        @if(count($users) > 0)
                            <table class="table table-bordered table-sm">
                                <thead class="bg-current text-white">
                                    <tr>
                                        <th style="width: 25%;">Name</th>
                                        <th style="width: 30%;">Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td style="word-break: break-word;">{{ $user->email }}</td>
                                        <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
                                        <td>
                                            <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @if($isAdmin)
                                                    <button wire:click="selectUser({{ $user->id }})" class="btn btn-sm p-0" title="Assign Role">
                                                        <span class="badge bg-primary rounded-pill" style="font-size: 0.75rem;">
                                                            Assign Role
                                                        </span>
                                                    </button>
                                                @endif
                                                @if($user->status === 'active')
                                                    <button wire:click="suspendUser({{ $user->id }})" class="btn btn-sm p-0" title="Suspend User">
                                                        <span class="badge bg-warning rounded-pill" style="font-size: 0.75rem;">
                                                            Suspend
                                                        </span>
                                                    </button>
                                                @else
                                                    <button wire:click="restoreUser({{ $user->id }})" class="btn btn-sm p-0" title="Restore User">
                                                        <span class="badge bg-success rounded-pill" style="font-size: 0.75rem;">
                                                            Restore
                                                        </span>
                                                    </button>
                                                @endif
                                                @if($isAdmin)
                                                    <button wire:click="removeUser({{ $user->id }})" class="btn btn-sm p-0" title="Remove User">
                                                        <span class="badge bg-danger rounded-pill" style="font-size: 0.75rem;">
                                                            Remove
                                                        </span>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-4">
                                <i class="ti-user font-xl text-muted mb-3"></i>
                                <h5 class="text-muted">No users found</h5>
                                <div class="small text-muted">
                                    There are no users in the system that match your search criteria.
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($users->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-muted small">
                                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                                </div>
                                <nav aria-label="Page numbers">
                                    <ul class="pagination pagination-sm mb-0">
                                        @php
                                            $start = max(1, $users->currentPage() - 2);
                                            $end = min($users->lastPage(), $users->currentPage() + 2);
                                        @endphp

                                        @if($start > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $users->url(1) }}">1</a>
                                            </li>
                                            @if($start > 2)
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            @endif
                                        @endif

                                        @for($i = $start; $i <= $end; $i++)
                                            <li class="page-item {{ $i == $users->currentPage() ? 'active' : '' }}">
                                                <a class="page-link" href="{{ $users->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endfor

                                        @if($end < $users->lastPage())
                                            @if($end < $users->lastPage() - 1)
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            @endif
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $users->url($users->lastPage()) }}">{{ $users->lastPage() }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                                <div class="nav-links">
                                    @if($users->onFirstPage())
                                        <span class="page-link disabled">Previous</span>
                                    @else
                                        <a class="page-link" href="{{ $users->previousPageUrl() }}">Previous</a>
                                    @endif

                                    @if($users->hasMorePages())
                                        <a class="page-link" href="{{ $users->nextPageUrl() }}">Next</a>
                                    @else
                                        <span class="page-link disabled">Next</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($isAdmin && $selectedUser)
<div class="modal fade" id="assignRoleModal" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Assign Role</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-2">
                <div class="mb-2">
                    <label class="form-label small">User</label>
                    <input type="text" class="form-control form-control-sm" value="{{ $selectedUser->name }}" readonly>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Role</label>
                    <select wire:model="selectedRole" class="form-select form-select-sm">
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" wire:click="assignRole">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', function () {
    let assignRoleModal;
    
    Livewire.on('showAssignRoleModal', () => {
        if (!assignRoleModal) {
            assignRoleModal = new bootstrap.Modal(document.getElementById('assignRoleModal'));
        }
        assignRoleModal.show();
    });

    Livewire.on('hideAssignRoleModal', () => {
        if (assignRoleModal) {
            assignRoleModal.hide();
        }
    });

    // Listen for modal close event
    document.getElementById('assignRoleModal').addEventListener('hidden.bs.modal', function () {
        @this.set('selectedUser', null);
    });
});
</script>
@endif