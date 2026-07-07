<x-alumniadmin-dashboard title="Alumni Categories | FuLafia Alumni">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Alumni categories</h1>
                                <p class="ads-page-subtitle">Manage category definitions used for onboarding, fees, and alumni grouping.</p>
                            </div>
                            <a href="{{ route('admin.alumni-categories.create') }}" class="btn btn-primary btn-sm ads-btn-primary text-white">
                                <i data-feather="plus" style="width: 15px; height: 15px;"></i>
                                Add category
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
                                <span class="ads-stat-label">Assigned</span>
                                <span class="ads-stat-value">{{ number_format($stats['assigned']) }}</span>
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

                            @if ($categories->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Alumni count</th>
                                                <th>Status</th>
                                                <th class="adt-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($categories as $category)
                                                <tr>
                                                    <td class="fw-medium">{{ $category->name }}</td>
                                                    <td class="adt-muted">{{ Str::limit($category->description, 60) ?: '—' }}</td>
                                                    <td class="adt-muted">{{ number_format($category->alumni_count) }}</td>
                                                    <td>
                                                        <span class="adt-status {{ $category->is_active ? 'adt-status-active' : 'adt-status-inactive' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="adt-actions">
                                                            <a
                                                                href="{{ route('admin.alumni-categories.edit', $category) }}"
                                                                class="adt-action-btn"
                                                                title="Edit"
                                                            >
                                                                <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                                            </a>
                                                            @if ($category->alumni_count === 0 && ! $category->hasFees())
                                                                <form
                                                                    action="{{ route('admin.alumni-categories.destroy', $category) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Are you sure you want to delete this category?')"
                                                                >
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="adt-action-btn adt-action-danger" title="Delete">
                                                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($categories->hasPages())
                                    <div class="adt-footer">
                                        <span class="adt-footer-count">
                                            {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }}
                                        </span>
                                        <div class="adt-pagination">
                                            {{ $categories->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="adt-empty">
                                    <div class="adt-empty-icon">
                                        <i data-feather="tag" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No categories found</h3>
                                    <p class="adt-empty-text">Create a category to organize alumni and fee targeting.</p>
                                    <a href="{{ route('admin.alumni-categories.create') }}" class="btn btn-sm ads-btn-primary mt-2">
                                        Add category
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