<x-layouts.accountant-linkages title="Payment Status | Accountant-Linkages">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content admin-surface admin-data-table" style="padding-right: 1.25rem;">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">
                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Payment status</h1>
                                <p class="ads-page-subtitle">Welcome, {{ auth()->user()->name }}.</p>
                            </div>
                        </div>

                        <div class="adt-panel">
                            <div class="adt-toolbar">
                                <form method="GET" class="adt-filters">
                                    <div class="adt-search">
                                        <i data-feather="search" class="adt-search-icon"></i>
                                        <input
                                            type="text"
                                            name="search"
                                            value="{{ $filters['search'] }}"
                                            placeholder="Name, email, matric"
                                            class="form-control form-control-sm"
                                        >
                                    </div>
                                    <select name="year" class="form-select form-select-sm adt-select adt-select-narrow">
                                        <option value="">{{ $minYear }}+</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" @selected((string) $filters['year'] === (string) $year)>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                    <select name="status" class="form-select form-select-sm adt-select adt-select-narrow" aria-label="Payment status">
                                        <option value="" @selected($filters['status'] === '')>All payments</option>
                                        <option value="paid" @selected($filters['status'] === 'paid')>Paid</option>
                                        <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm ads-btn-primary">Filter</button>
                                    @if (request()->filled('search') || request()->filled('year') || request()->filled('status'))
                                        <a href="{{ route('accountant-linkages.payments') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                                    @endif
                                </form>
                            </div>

                            @if ($alumni->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Matric</th>
                                                <th>Faculty</th>
                                                <th>Department</th>
                                                <th>Year</th>
                                                <th>Payments</th>
                                                <th class="adt-th-actions">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($alumni as $row)
                                                <tr>
                                                    <td class="fw-medium">{{ $row['name'] }}</td>
                                                    <td>{{ $row['matric_number'] }}</td>
                                                    <td>{{ $row['faculty'] }}</td>
                                                    <td>{{ $row['department'] }}</td>
                                                    <td>{{ $row['year'] }}</td>
                                                    <td>
                                                        <span class="adt-status {{ $row['payments'] === 'Paid' ? 'adt-status-active' : 'adt-status-suspended' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $row['payments'] }}
                                                        </span>
                                                    </td>
                                                    <td class="adt-actions">
                                                        <a href="{{ route('accountant-linkages.payments.show', $row['id']) }}" class="btn btn-sm btn-link p-0">View</a>
                                                        <a href="{{ route('accountant-linkages.payments.print', $row['id']) }}" class="btn btn-sm btn-link p-0" target="_blank" rel="noopener">Print</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($alumni->hasPages())
                                    <div class="adt-footer">
                                        <span class="adt-footer-count">
                                            {{ $alumni->firstItem() }}–{{ $alumni->lastItem() }} of {{ $alumni->total() }}
                                        </span>
                                        <div class="adt-pagination">
                                            {{ $alumni->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="adt-empty">
                                    <div class="adt-empty-icon">
                                        <i data-feather="users" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No alumni found</h3>
                                    <p class="adt-empty-text">No alumni from {{ $minYear }} onward match this filter.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.accountant-linkages>
