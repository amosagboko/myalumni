<x-alumniadmin-dashboard title="Payment Structures | FuLafia Alumni">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Payment structures</h1>
                                <p class="ads-page-subtitle">Choose separate per-item checkout or a combined payment for selected statutory fees.</p>
                            </div>
                            <a href="{{ route('admin.payment-structures.create') }}" class="btn btn-primary btn-sm ads-btn-primary text-white">
                                <i data-feather="plus" style="width: 15px; height: 15px;"></i>
                                Add structure
                            </a>
                        </div>

                        <div class="ads-stats">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Total</span>
                                <span class="ads-stat-value">{{ number_format($stats['total']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Combined</span>
                                <span class="ads-stat-value">{{ number_format($stats['combined']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Active</span>
                                <span class="ads-stat-value">{{ number_format($stats['active']) }}</span>
                            </div>
                        </div>

                        <div class="adt-panel">
                            @if (session('success'))
                                <div class="adt-alert adt-alert-success mx-3 mt-3 mb-0" role="alert">{{ session('success') }}</div>
                            @endif
                            @if (session('error'))
                                <div class="adt-alert adt-alert-error mx-3 mt-3 mb-0" role="alert">{{ session('error') }}</div>
                            @endif

                            <div class="table-responsive">
                                <table class="table adt-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Mode</th>
                                            <th>Scope</th>
                                            <th>Items</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($structures as $structure)
                                            <tr>
                                                <td>
                                                    <div class="fw-medium">{{ $structure->name }}</div>
                                                    <div class="small text-muted">{{ $structure->display_title }}</div>
                                                </td>
                                                <td>
                                                    @if ($structure->isCombined())
                                                        <span class="adt-tag">Combined</span>
                                                    @else
                                                        <span class="adt-tag">Separate</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>{{ $structure->category?->name ?? 'All categories' }}</div>
                                                    <div class="small text-muted">
                                                        {{ $structure->graduation_year ? 'Class of '.$structure->graduation_year : 'All cohorts' }}
                                                    </div>
                                                </td>
                                                <td>{{ $structure->fee_templates_count }}</td>
                                                <td>
                                                    @if ($structure->is_active)
                                                        <span class="adt-status adt-status-active"><span class="adt-status-dot"></span> Active</span>
                                                    @else
                                                        <span class="adt-status adt-status-inactive"><span class="adt-status-dot"></span> Inactive</span>
                                                    @endif
                                                </td>
                                                <td class="text-nowrap">
                                                    <a href="{{ route('admin.payment-structures.edit', $structure) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                    @if ($structure->transactions_count === 0)
                                                        <form action="{{ route('admin.payment-structures.destroy', $structure) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this payment structure?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    No payment structures yet.
                                                    <a href="{{ route('admin.payment-structures.create') }}">Create one</a> to enable combined checkout for undergraduate full-time.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($structures->hasPages())
                                <div class="p-3">{{ $structures->links() }}</div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>
