<div>
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left pe-0">
            <div class="row">
                <div class="col-12">

                    {{-- Page header --}}
                    <div class="ads-page-header">
                        <div>
                            <h1 class="ads-page-title">Manage Users</h1>
                            <p class="ads-page-subtitle">View accounts, assign roles, and manage access.</p>
                        </div>
                        @if($canCreateUsers)
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm ads-btn-primary text-white">
                                <i data-feather="user-plus" style="width: 15px; height: 15px;"></i>
                                Add user
                            </a>
                        @endif
                    </div>

                    {{-- Stats --}}
                    <div class="ads-stats">
                        <div class="ads-stat">
                            <span class="ads-stat-label">Total</span>
                            <span class="ads-stat-value">{{ number_format($userStats['total']) }}</span>
                        </div>
                        <div class="ads-stat">
                            <span class="ads-stat-label">Active</span>
                            <span class="ads-stat-value">{{ number_format($userStats['active']) }}</span>
                        </div>
                        <div class="ads-stat">
                            <span class="ads-stat-label">Suspended</span>
                            <span class="ads-stat-value">{{ number_format($userStats['suspended']) }}</span>
                        </div>
                        <div class="ads-stat">
                            <span class="ads-stat-label">New today</span>
                            <span class="ads-stat-value">{{ number_format($userStats['new_today']) }}</span>
                        </div>
                    </div>

                    {{-- Main panel --}}
                    <div class="adt-panel">

                        {{-- Toolbar --}}
                        <div class="adt-toolbar">
                            <div class="adt-filters">
                                <div class="adt-search">
                                    <i data-feather="search" class="adt-search-icon"></i>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="search"
                                        class="form-control form-control-sm"
                                        placeholder="Search name, email, or role…"
                                    >
                                </div>
                                <select wire:model.live="statusFilter" class="form-select form-select-sm adt-select">
                                    <option value="">All statuses</option>
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <select wire:model.live="roleFilter" class="form-select form-select-sm adt-select">
                                    <option value="">All roles</option>
                                    @foreach($filterRoles as $role)
                                        <option value="{{ $role->name }}">{{ \Illuminate\Support\Str::headline($role->name) }}</option>
                                    @endforeach
                                </select>
                                <select wire:model.live="perPage" class="form-select form-select-sm adt-select adt-select-narrow">
                                    <option value="10">10 rows</option>
                                    <option value="25">25 rows</option>
                                    <option value="50">50 rows</option>
                                </select>
                            </div>
                        </div>

                        @if (session()->has('message'))
                            <div class="adt-alert adt-alert-success" role="alert">
                                {{ session('message') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="adt-alert adt-alert-error" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        {{-- Table --}}
                        @if($users->count() > 0)
                            <div class="adt-table-wrap">
                                <table class="adt-table">
                                    <thead>
                                        <tr>
                                            <th class="adt-th-sortable" wire:click="sortBy('name')">
                                                User
                                                @if($sortField === 'name')
                                                    <span class="adt-sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </th>
                                            <th class="adt-th-sortable" wire:click="sortBy('email')">
                                                Email
                                                @if($sortField === 'email')
                                                    <span class="adt-sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </th>
                                            <th>Role</th>
                                            <th>Created by</th>
                                            <th class="adt-th-sortable" wire:click="sortBy('status')">
                                                Status
                                                @if($sortField === 'status')
                                                    <span class="adt-sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </th>
                                            <th class="adt-th-sortable" wire:click="sortBy('created_at')">
                                                Joined
                                                @if($sortField === 'created_at')
                                                    <span class="adt-sort-indicator">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </th>
                                            <th class="adt-th-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                            <tr wire:key="user-{{ $user->id }}">
                                                <td>
                                                    <div class="adt-user-cell">
                                                        <div class="adt-avatar">
                                                            <img
                                                                src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('/images/user-8.png') }}"
                                                                alt=""
                                                            >
                                                        </div>
                                                        <span class="adt-user-name">{{ $user->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="adt-email">{{ $user->email }}</td>
                                                <td>
                                                    <span class="adt-tag">{{ $user->formattedRoles() }}</span>
                                                </td>
                                                <td class="adt-muted">{{ $user->creator?->name ?? '—' }}</td>
                                                <td>
                                                    <span class="adt-status {{ $user->isActive() ? 'adt-status-active' : 'adt-status-suspended' }}">
                                                        <span class="adt-status-dot"></span>
                                                        {{ $user->displayStatus() }}
                                                    </span>
                                                </td>
                                                <td class="adt-muted">{{ $user->created_at?->format('M j, Y') ?? '—' }}</td>
                                                <td>
                                                    <div class="adt-actions">
                                                        @if($canAssignRoles && ($isAdmin || ($isAro && $user->hasRole('alumni') && !$user->hasRole(['administrator', 'alumni-relations-officer']))))
                                                            <button
                                                                type="button"
                                                                wire:click="selectUser({{ $user->id }})"
                                                                class="adt-action-btn"
                                                                title="Assign role"
                                                            >
                                                                <i data-feather="shield" style="width: 14px; height: 14px;"></i>
                                                            </button>
                                                        @endif

                                                        @if($canAssignRoles && ($isAdmin || ($isAro && $user->hasRole('alumni'))))
                                                            @if($user->isActive())
                                                                <button
                                                                    type="button"
                                                                    wire:click="suspendUser({{ $user->id }})"
                                                                    wire:confirm="Suspend {{ $user->name }}?"
                                                                    class="adt-action-btn"
                                                                    title="Suspend"
                                                                    @if($user->id === Auth::id()) disabled @endif
                                                                >
                                                                    <i data-feather="pause-circle" style="width: 14px; height: 14px;"></i>
                                                                </button>
                                                            @else
                                                                <button
                                                                    type="button"
                                                                    wire:click="restoreUser({{ $user->id }})"
                                                                    wire:confirm="Restore {{ $user->name }}?"
                                                                    class="adt-action-btn"
                                                                    title="Restore"
                                                                >
                                                                    <i data-feather="play-circle" style="width: 14px; height: 14px;"></i>
                                                                </button>
                                                            @endif
                                                        @endif

                                                        @if($isAdmin && $user->id !== Auth::id() && !$user->hasRole(['administrator', 'alumni-relations-officer']))
                                                            <button
                                                                type="button"
                                                                wire:click="removeUser({{ $user->id }})"
                                                                wire:confirm="Permanently remove {{ $user->name }}? This cannot be undone."
                                                                class="adt-action-btn adt-action-danger"
                                                                title="Remove"
                                                            >
                                                                <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($users->hasPages())
                                <div class="adt-footer">
                                    <span class="adt-footer-count">
                                        {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
                                    </span>
                                    <div class="adt-pagination">
                                        {{ $users->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="adt-empty">
                                <div class="adt-empty-icon">
                                    <i data-feather="users" style="width: 28px; height: 28px;"></i>
                                </div>
                                <h3 class="adt-empty-title">No users found</h3>
                                <p class="adt-empty-text">Try adjusting your search or filters.</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@if($canAssignRoles && $selectedUser)
    <div class="ads-modal-overlay" wire:click.self="closeAssignRoleModal" role="dialog" aria-modal="true" aria-labelledby="assignRoleModalTitle">
        <div class="ads-modal-dialog">
            <div class="ads-modal-card">
                <div class="ads-modal-header">
                    <h6 class="ads-modal-title" id="assignRoleModalTitle">Assign role</h6>
                    <button type="button" class="btn-close" wire:click="closeAssignRoleModal" aria-label="Close"></button>
                </div>
                <div class="ads-modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">User</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $selectedUser->name }}" readonly>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small text-muted mb-1">Role</label>
                        <select wire:model="selectedRole" class="form-select form-select-sm">
                            <option value="">Select role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ \Illuminate\Support\Str::headline($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="ads-modal-footer">
                    <button type="button" class="btn btn-light btn-sm" wire:click="closeAssignRoleModal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm ads-btn-primary text-white" wire:click="assignRole">Save</button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>

@push('scripts')
<script>
    function cleanupBootstrapModalArtifacts() {
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }

    function initManageUsersFeather() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        cleanupBootstrapModalArtifacts();
        initManageUsersFeather();
    });
    document.addEventListener('livewire:navigated', () => {
        cleanupBootstrapModalArtifacts();
        initManageUsersFeather();
    });

    if (typeof Livewire !== 'undefined') {
        Livewire.hook('morph.updated', () => {
            initManageUsersFeather();
        });
    }
</script>
@endpush
