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
                <div class="adt-toolbar">
                    <div class="adt-filters">
                        <div class="adt-search">
                            <i data-feather="search" class="adt-search-icon"></i>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search…" value="{{ request('search') }}">
                        </div>
                        <select name="status" class="form-select form-select-sm adt-select">
                            <option value="">All statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                @php $items = $items ?? collect(); @endphp

                @if($items->count() > 0)
                    <div class="adt-table-wrap">
                        <table class="adt-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th class="adt-th-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td><span class="adt-user-name">{{ $item->name }}</span></td>
                                        <td><span class="adt-status adt-status-active"><span class="adt-status-dot"></span>Active</span></td>
                                        <td><div class="adt-actions"><button type="button" class="adt-action-btn" title="View"><i data-feather="eye" style="width:14px;height:14px"></i></button></div></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="adt-empty">
                        <h3 class="adt-empty-title">No records found</h3>
                        <p class="adt-empty-text">Try adjusting your search or filters.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>if (typeof feather !== 'undefined') feather.replace();</script>
@endpush
