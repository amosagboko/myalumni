<x-alumniadmin-dashboard title="Fee Templates | FuLafia Alumni">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Fee templates</h1>
                                <p class="ads-page-subtitle">Manage fee amounts by type, category, graduation year, and purpose.</p>
                            </div>
                            <a href="{{ route('admin.fee-templates.create') }}" class="btn btn-primary btn-sm ads-btn-primary text-white">
                                <i data-feather="plus" style="width: 15px; height: 15px;"></i>
                                Add template
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
                                <span class="ads-stat-label">Onboarding</span>
                                <span class="ads-stat-value">{{ number_format($stats['onboarding']) }}</span>
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

                            <div class="adt-toolbar">
                                <form method="GET" class="adt-filters">
                                    <select name="fee_type" class="form-select form-select-sm adt-select">
                                        <option value="">All fee types</option>
                                        @foreach ($feeTypes as $feeType)
                                            <option value="{{ $feeType->id }}" @selected(request('fee_type') == $feeType->id)>{{ $feeType->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="graduation_year" class="form-select form-select-sm adt-select adt-select-narrow">
                                        <option value="">All years</option>
                                        @for ($year = date('Y') + 1; $year >= 2020; $year--)
                                            <option value="{{ $year }}" @selected(request('graduation_year') == $year)>{{ $year }}</option>
                                        @endfor
                                    </select>
                                    <select name="category" class="form-select form-select-sm adt-select">
                                        <option value="">All categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="fee_purpose" class="form-select form-select-sm adt-select">
                                        <option value="">All purposes</option>
                                        <option value="onboarding" @selected(request('fee_purpose') === 'onboarding')>Onboarding</option>
                                        <option value="annual_renewal" @selected(request('fee_purpose') === 'annual_renewal')>Annual renewal</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm ads-btn-primary">
                                        <i data-feather="filter" style="width: 14px; height: 14px;"></i>
                                        Filter
                                    </button>
                                    @if (request()->hasAny(['fee_type', 'graduation_year', 'category', 'fee_purpose']))
                                        <a href="{{ route('admin.fee-templates.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                                    @endif
                                </form>
                            </div>

                            @if ($feeTemplates->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Fee type</th>
                                                <th>Purpose</th>
                                                <th>Year</th>
                                                <th>Category</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Validity</th>
                                                <th class="adt-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($feeTemplates as $template)
                                                <tr>
                                                    <td>
                                                        <div class="fw-medium">{{ $template->feeType->name }}</div>
                                                        <small class="adt-muted">{{ $template->feeType->code }}</small>
                                                    </td>
                                                    <td>
                                                        @if ($template->fee_purpose === 'onboarding')
                                                            <span class="adt-tag">Onboarding</span>
                                                        @elseif ($template->fee_purpose === 'annual_renewal')
                                                            <span class="adt-tag">Annual</span>
                                                        @else
                                                            <span class="adt-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ((int) $template->graduation_year === 0)
                                                            <span class="adt-tag">All years</span>
                                                        @else
                                                            {{ $template->graduation_year }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($template->category)
                                                            <span class="adt-tag">{{ $template->category->name }}</span>
                                                        @else
                                                            <span class="adt-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="fw-medium">₦{{ number_format($template->amount, 2) }}</td>
                                                    <td>
                                                        <span class="adt-status {{ $template->is_active ? 'adt-status-active' : 'adt-status-inactive' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td class="adt-muted">
                                                        <div>{{ \Carbon\Carbon::parse($template->valid_from)->format('M j, Y') }}</div>
                                                        @if ($template->valid_until)
                                                            <small>to {{ \Carbon\Carbon::parse($template->valid_until)->format('M j, Y') }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="adt-actions">
                                                            <a
                                                                href="{{ route('admin.fee-templates.edit', $template) }}"
                                                                class="adt-action-btn"
                                                                title="Edit"
                                                            >
                                                                <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                                            </a>
                                                            @if ($template->is_active)
                                                                <form
                                                                    action="{{ route('admin.fee-templates.deactivate', $template) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Deactivate this template?')"
                                                                >
                                                                    @csrf
                                                                    <button type="submit" class="adt-action-btn" title="Deactivate">
                                                                        <i data-feather="pause-circle" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <form
                                                                    action="{{ route('admin.fee-templates.activate', $template) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Activate this template?')"
                                                                >
                                                                    @csrf
                                                                    <button type="submit" class="adt-action-btn" title="Activate">
                                                                        <i data-feather="play-circle" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            @if ($template->transactions->count() === 0)
                                                                <form
                                                                    action="{{ route('admin.fee-templates.destroy', $template) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Delete this template? This action cannot be undone.')"
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

                                @if ($feeTemplates->hasPages())
                                    <div class="adt-footer">
                                        <span class="adt-footer-count">
                                            {{ $feeTemplates->firstItem() }}–{{ $feeTemplates->lastItem() }} of {{ $feeTemplates->total() }}
                                        </span>
                                        <div class="adt-pagination">
                                            {{ $feeTemplates->withQueryString()->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="adt-empty">
                                    <div class="adt-empty-icon">
                                        <i data-feather="file-text" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No fee templates found</h3>
                                    <p class="adt-empty-text">Try adjusting your filters or create a new template.</p>
                                    <a href="{{ route('admin.fee-templates.create') }}" class="btn btn-sm ads-btn-primary mt-2">
                                        Add template
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
