<div class="container-fluid" style="padding: 20px;">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('alumni-relations-officer.home') }}" class="btn btn-link text-muted me-2">
                            <i class="feather-arrow-left"></i>
                        </a>
                        <h6 class="mb-0">Manage Alumni</h6>
                    </div>
                    <div class="text-muted small">
                        Logged in as: {{ Auth::user()->name }} ({{ Auth::user()->roles->pluck('name')->implode(', ') }})
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex gap-2">
                                <div class="input-group" style="width: 300px;">
                                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search alumni...">
                                    <span class="input-group-text">
                                        <i class="feather-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if (session()->has('message'))
                            <div class="alert alert-success py-2 mb-3">
                                {{ session('message') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger py-2 mb-3">
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>

                    @if(count($users) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($user->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($user->status === 'active')
                                                    <button wire:click="suspendUser({{ $user->id }})" class="btn btn-sm btn-warning">
                                                        Suspend
                                                    </button>
                                                @else
                                                    <button wire:click="activateUser({{ $user->id }})" class="btn btn-sm btn-success">
                                                        Activate
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No users found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
                    @else
                        <div class="text-center py-4">
                            <div class="text-muted mb-2">No alumni found.</div>
                            <div class="small text-muted">
                                There are no alumni in the system that match your search criteria.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card-body {
        display: flex;
        flex-direction: column;
    }
    .table-responsive {
        flex: 1;
        min-height: 0;
    }
    .pagination {
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .pagination .page-item {
        margin: 0;
    }
    .pagination .page-item .page-link {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
        border: 1px solid #dee2e6;
        color: #0d6efd;
        background-color: #fff;
    }
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
        color: #0a58ca;
    }
    .nav-links {
        display: flex;
        gap: 0.5rem;
    }
    .nav-links .page-link {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
        border: 1px solid #dee2e6;
        color: #0d6efd;
        background-color: #fff;
        text-decoration: none;
    }
    .nav-links .page-link:hover:not(.disabled) {
        background-color: #e9ecef;
        border-color: #dee2e6;
        color: #0a58ca;
    }
    .nav-links .page-link.disabled {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
    @media (max-width: 991px) {
        .main-content {
            margin-left: 0 !important;
        }
    }
</style>
@endpush 