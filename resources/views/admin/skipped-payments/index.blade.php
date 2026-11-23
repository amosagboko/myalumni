<x-alumniadmin-dashboard title="Skipped Payments | FuLafia Alumni">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Skipped payments</h1>
                                <p class="ads-page-subtitle">Alumni who enrolled (especially online) without completing statutory onboarding or cohort fees. “Bypassed gate” means the portal previously treated them as paid because no active fee line was shown.</p>
                            </div>
                            <a href="{{ route('admin.skipped-payments.export', request()->query()) }}" class="btn btn-sm ads-btn-primary">
                                <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                Download CSV
                            </a>
                        </div>

                        <div class="ads-stats">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Unpaid</span>
                                <span class="ads-stat-value">{{ number_format($stats['total']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Bypassed gate</span>
                                <span class="ads-stat-value">{{ number_format($stats['bypassed']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Online enrolments</span>
                                <span class="ads-stat-value">{{ number_format($stats['online']) }}</span>
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
                                        <option value="">{{ \App\Services\Alumni\SkippedPaymentReportService::DEFAULT_MIN_YEAR }}+</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" @selected((string) $filters['year'] === (string) $year)>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                    <select name="source" class="form-select form-select-sm adt-select adt-select-narrow">
                                        <option value="all" @selected($filters['source'] === 'all')>All sources</option>
                                        <option value="online" @selected($filters['source'] === 'online')>Online</option>
                                        <option value="uploaded" @selected($filters['source'] === 'uploaded')>Uploaded</option>
                                    </select>
                                    <select name="category_id" class="form-select form-select-sm adt-select">
                                        <option value="">All categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="form-check form-check-inline mb-0 small">
                                        <input type="checkbox" name="bypassed" value="1" class="form-check-input" @checked($filters['bypassed'])>
                                        Bypassed gate only
                                    </label>
                                    <button type="submit" class="btn btn-sm ads-btn-primary">
                                        <i data-feather="filter" style="width: 14px; height: 14px;"></i>
                                        Filter
                                    </button>
                                    @if (request()->hasAny(['search', 'year', 'source', 'category_id', 'bypassed']))
                                        <a href="{{ route('admin.skipped-payments.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                                    @endif
                                </form>
                            </div>

                            @if ($alumni->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Alumni</th>
                                                <th>Matric</th>
                                                <th>Year</th>
                                                <th>Category</th>
                                                <th>Source</th>
                                                <th>Gate</th>
                                                <th>Outstanding</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($alumni as $row)
                                                <tr>
                                                    <td>
                                                        <div class="fw-medium">{{ $row['name'] ?? '—' }}</div>
                                                        <div class="adt-muted small">{{ $row['email'] ?? '—' }}</div>
                                                    </td>
                                                    <td class="fw-medium">{{ $row['matric_number'] ?? '—' }}</td>
                                                    <td>{{ $row['year_of_graduation'] ?? '—' }}</td>
                                                    <td>{{ $row['category'] ?? '—' }}</td>
                                                    <td>{{ $row['source'] }}</td>
                                                    <td>
                                                        @if ($row['bypassed_payment_gate'])
                                                            <span class="adt-status adt-status-suspended">
                                                                <span class="adt-status-dot"></span>
                                                                Bypassed
                                                            </span>
                                                        @else
                                                            <span class="adt-status adt-status-active">
                                                                <span class="adt-status-dot"></span>
                                                                Still gated
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $row['outstanding_fees'] !== '' ? $row['outstanding_fees'] : '—' }}</td>
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
                                        <i data-feather="check-circle" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No skipped payments</h3>
                                    <p class="adt-empty-text">Nobody in the current filter is missing statutory onboarding or cohort fees.</p>
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
