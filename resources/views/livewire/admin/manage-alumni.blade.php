<div>
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Manage alumni</h1>
                                <p class="ads-page-subtitle">Review alumni accounts and update their access status.</p>
                            </div>
                            <a href="{{ route('alumni-relations-officer.home') }}" class="btn btn-sm btn-outline-secondary">
                                <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                Dashboard
                            </a>
                        </div>

                        <div class="ads-stats ads-stats-3">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Total alumni</span>
                                <span class="ads-stat-value">{{ number_format($stats['total']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Active</span>
                                <span class="ads-stat-value">{{ number_format($stats['active']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Suspended</span>
                                <span class="ads-stat-value">{{ number_format($stats['suspended']) }}</span>
                            </div>
                        </div>

                        <div class="adt-panel">
                            <div class="adt-toolbar">
                                <div class="adt-filters">
                                    <div class="adt-search">
                                        <i data-feather="search" class="adt-search-icon"></i>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.300ms="search"
                                            class="form-control form-control-sm"
                                            placeholder="Search name or email…"
                                        >
                                    </div>
                                </div>
                                <div class="adt-muted small">
                                    Logged in as {{ Auth::user()->name }}
                                </div>
                            </div>

                            @if (session()->has('message'))
                                <div class="adt-alert adt-alert-success mx-3 mt-3 mb-0" role="alert">
                                    {{ session('message') }}
                                </div>
                            @endif

                            @if (session()->has('error'))
                                <div class="adt-alert adt-alert-error mx-3 mt-3 mb-0" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if ($users->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th class="adt-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $user)
                                                <tr wire:key="alumni-user-{{ $user->id }}">
                                                    <td class="fw-medium">{{ $user->name }}</td>
                                                    <td class="adt-email">{{ $user->email }}</td>
                                                    <td>
                                                        <span class="adt-status {{ $user->status === 'active' ? 'adt-status-active' : 'adt-status-suspended' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ ucfirst($user->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="adt-actions">
                                                            @if ($user->status === 'active')
                                                                <button
                                                                    type="button"
                                                                    wire:click="suspendUser({{ $user->id }})"
                                                                    wire:confirm="Suspend {{ $user->name }}?"
                                                                    class="adt-action-btn"
                                                                    title="Suspend"
                                                                >
                                                                    <i data-feather="pause-circle" style="width: 14px; height: 14px;"></i>
                                                                </button>
                                                            @else
                                                                <button
                                                                    type="button"
                                                                    wire:click="activateUser({{ $user->id }})"
                                                                    wire:confirm="Activate {{ $user->name }}?"
                                                                    class="adt-action-btn"
                                                                    title="Activate"
                                                                >
                                                                    <i data-feather="play-circle" style="width: 14px; height: 14px;"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($users->hasPages())
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
                                    <h3 class="adt-empty-title">No alumni found</h3>
                                    <p class="adt-empty-text">Try adjusting your search to find matching accounts.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
        document.addEventListener('livewire:navigated', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
    @endpush
</div>
