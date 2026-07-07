<x-alumniadmin-dashboard>
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Fee types</h1>
                                <p class="ads-page-subtitle">Manage fee categories used when creating templates and payment rules.</p>
                            </div>
                            <a href="{{ route('admin.fee-types.create') }}" class="btn btn-primary btn-sm ads-btn-primary text-white">
                                <i data-feather="plus" style="width: 15px; height: 15px;"></i>
                                Add fee type
                            </a>
                        </div>

                        <div class="ads-stats">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Total</span>
                                <span class="ads-stat-value">{{ number_format($stats['total']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Active</span>
                                <span class="ads-stat-value">{{ number_format($stats['active']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Inactive</span>
                                <span class="ads-stat-value">{{ number_format($stats['inactive']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">System</span>
                                <span class="ads-stat-value">{{ number_format($stats['system']) }}</span>
                            </div>
                        </div>

                        <div class="adt-panel">
                            @if (session('success'))
                                <div class="adt-alert adt-alert-success mx-3 mt-3 mb-0" role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="adt-alert adt-alert-error mx-3 mt-3 mb-0" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if ($feeTypes->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Templates</th>
                                                <th>Status</th>
                                                <th>Type</th>
                                                <th class="adt-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($feeTypes as $feeType)
                                                <tr>
                                                    <td class="fw-medium">{{ $feeType->name }}</td>
                                                    <td><code class="small">{{ $feeType->code }}</code></td>
                                                    <td class="adt-muted">{{ $feeType->description ? Str::limit($feeType->description, 60) : '—' }}</td>
                                                    <td class="adt-muted">{{ number_format($feeType->fee_templates_count) }}</td>
                                                    <td>
                                                        <span class="adt-status {{ $feeType->is_active ? 'adt-status-active' : 'adt-status-inactive' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $feeType->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="adt-tag">{{ $feeType->is_system ? 'System' : 'Custom' }}</span>
                                                    </td>
                                                    <td>
                                                        @if (!$feeType->is_system)
                                                            <div class="adt-actions">
                                                                <a
                                                                    href="{{ route('admin.fee-types.edit', $feeType) }}"
                                                                    class="adt-action-btn"
                                                                    title="Edit"
                                                                >
                                                                    <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                                                </a>
                                                                <form
                                                                    action="{{ route('admin.fee-types.toggle-status', $feeType) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Are you sure you want to {{ $feeType->is_active ? 'deactivate' : 'activate' }} this fee type?')"
                                                                >
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button
                                                                        type="submit"
                                                                        class="adt-action-btn"
                                                                        title="{{ $feeType->is_active ? 'Deactivate' : 'Activate' }}"
                                                                    >
                                                                        <i data-feather="{{ $feeType->is_active ? 'pause-circle' : 'play-circle' }}" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                                <form
                                                                    action="{{ route('admin.fee-types.destroy', $feeType) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Are you sure you want to delete this fee type?')"
                                                                >
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button
                                                                        type="submit"
                                                                        class="adt-action-btn adt-action-danger"
                                                                        title="Delete"
                                                                        @if($feeType->fee_templates_count > 0) disabled @endif
                                                                    >
                                                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <span class="adt-muted small">Protected</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($feeTypes->hasPages())
                                    <div class="adt-footer">
                                        <span class="adt-footer-count">
                                            {{ $feeTypes->firstItem() }}–{{ $feeTypes->lastItem() }} of {{ $feeTypes->total() }}
                                        </span>
                                        <div class="adt-pagination">
                                            {{ $feeTypes->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="adt-empty">
                                    <div class="adt-empty-icon">
                                        <i data-feather="tag" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No fee types found</h3>
                                    <p class="adt-empty-text">Create a fee type to use when setting up templates.</p>
                                    <a href="{{ route('admin.fee-types.create') }}" class="btn btn-sm ads-btn-primary mt-2">
                                        Add fee type
                                    </a>
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
    </script>
    @endpush
</x-alumniadmin-dashboard>
