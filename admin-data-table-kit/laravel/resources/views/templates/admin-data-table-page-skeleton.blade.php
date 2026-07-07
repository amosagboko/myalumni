{{--
    Admin data table page skeleton — copy into your view and replace placeholders.
    Requires surface + table styles and @stack('styles') in layout.
--}}
<x-admin.surface-styles />
<x-admin.data-table-styles />

<div class="main-content admin-data-table">
    <div class="row">
        <div class="col-12">

            <div class="ads-page-header">
                <div>
                    <h1 class="ads-page-title">{{ $pageTitle ?? 'Page title' }}</h1>
                    <p class="ads-page-subtitle">{{ $pageSubtitle ?? 'Short description of this list.' }}</p>
                </div>
                <a href="{{ $createUrl ?? '#' }}" class="btn btn-primary btn-sm ads-btn-primary text-white">
                    <i data-feather="plus" style="width: 15px; height: 15px;"></i>
                    {{ $createLabel ?? 'Add record' }}
                </a>
            </div>

            <div class="ads-stats">
                <div class="ads-stat">
                    <span class="ads-stat-label">Total</span>
                    <span class="ads-stat-value">{{ number_format($stats['total'] ?? 0) }}</span>
                </div>
                <div class="ads-stat">
                    <span class="ads-stat-label">Active</span>
                    <span class="ads-stat-value">{{ number_format($stats['active'] ?? 0) }}</span>
                </div>
                <div class="ads-stat">
                    <span class="ads-stat-label">Pending</span>
                    <span class="ads-stat-value">{{ number_format($stats['pending'] ?? 0) }}</span>
                </div>
                <div class="ads-stat">
                    <span class="ads-stat-label">Today</span>
                    <span class="ads-stat-value">{{ number_format($stats['today'] ?? 0) }}</span>
                </div>
            </div>

            <div class="adt-panel">

                {{-- Toolbar / filters --}}
                <div class="adt-toolbar">
                    <div class="adt-filters">
                        <div class="adt-search">
                            <i data-feather="search" class="adt-search-icon"></i>
                            <input
                                type="text"
                                name="search"
                                class="form-control form-control-sm"
                                placeholder="Search…"
                                value="{{ request('search') }}"
                            >
                        </div>
                        <select name="status" class="form-select form-select-sm adt-select">
                            <option value="">All statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <select name="per_page" class="form-select form-select-sm adt-select adt-select-narrow">
                            <option value="10">10 rows</option>
                            <option value="25">25 rows</option>
                            <option value="50">50 rows</option>
                        </select>
                    </div>
                </div>

                @if (session('message'))
                    <div class="adt-alert adt-alert-success" role="alert">{{ session('message') }}</div>
                @endif
                @if (session('error'))
                    <div class="adt-alert adt-alert-error" role="alert">{{ session('error') }}</div>
                @endif

                @php $items = $items ?? collect(); @endphp

                @if($items->count() > 0)
                    <div class="adt-table-wrap">
                        <table class="adt-table">
                            <thead>
                                <tr>
                                    <th class="adt-th-sortable">Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th class="adt-muted">Date</th>
                                    <th class="adt-th-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td>
                                            <div class="adt-user-cell">
                                                {{-- Optional avatar --}}
                                                <div class="adt-avatar">
                                                    <img src="{{ $item->avatar_url ?? asset('/images/user-8.png') }}" alt="">
                                                </div>
                                                <span class="adt-user-name">{{ $item->name }}</span>
                                            </div>
                                        </td>
                                        <td><span class="adt-tag">{{ $item->category ?? '—' }}</span></td>
                                        <td>
                                            <span class="adt-status adt-status-active">
                                                <span class="adt-status-dot"></span>
                                                Active
                                            </span>
                                        </td>
                                        <td class="adt-muted">{{ $item->created_at?->format('M j, Y') ?? '—' }}</td>
                                        <td>
                                            <div class="adt-actions">
                                                <a href="#" class="adt-action-btn" title="View">
                                                    <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                                                </a>
                                                <button type="button" class="adt-action-btn adt-action-danger" title="Delete">
                                                    <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($items, 'hasPages') && $items->hasPages())
                        <div class="adt-footer">
                            <span class="adt-footer-count">
                                {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}
                            </span>
                            <div class="adt-pagination">
                                {{ $items->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="adt-empty">
                        <div class="adt-empty-icon">
                            <i data-feather="inbox" style="width: 28px; height: 28px;"></i>
                        </div>
                        <h3 class="adt-empty-title">No records found</h3>
                        <p class="adt-empty-text">Try adjusting your search or filters.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    if (typeof feather !== 'undefined') feather.replace();
</script>
@endpush
