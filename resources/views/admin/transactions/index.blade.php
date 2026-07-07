<x-alumniadmin-dashboard title="Transaction Management | FuLafia Alumni">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Transactions</h1>
                                <p class="ads-page-subtitle">Review payment activity, filter by status or fee type, and manage pending transactions.</p>
                            </div>
                            <a href="{{ route('admin.transactions.export', request()->query()) }}" class="btn btn-sm ads-btn-primary">
                                <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                Export
                            </a>
                        </div>

                        <div class="ads-stats">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Total</span>
                                <span class="ads-stat-value">{{ number_format($stats['total']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Paid</span>
                                <span class="ads-stat-value">{{ number_format($stats['paid']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Pending</span>
                                <span class="ads-stat-value">{{ number_format($stats['pending']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Failed</span>
                                <span class="ads-stat-value">{{ number_format($stats['failed']) }}</span>
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
                                    <div class="adt-search">
                                        <i data-feather="search" class="adt-search-icon"></i>
                                        <input
                                            type="text"
                                            name="search"
                                            value="{{ request('search') }}"
                                            placeholder="Reference, name, email"
                                            class="form-control form-control-sm"
                                        >
                                    </div>
                                    <select name="status" class="form-select form-select-sm adt-select adt-select-narrow">
                                        <option value="">All statuses</option>
                                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                        <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                                        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                                    </select>
                                    <select name="fee_type" class="form-select form-select-sm adt-select">
                                        <option value="">All fee types</option>
                                        @foreach ($feeTypes as $feeType)
                                            <option value="{{ $feeType->id }}" @selected(request('fee_type') == $feeType->id)>{{ $feeType->name }}</option>
                                        @endforeach
                                    </select>
                                    <input
                                        type="date"
                                        name="date_from"
                                        value="{{ request('date_from') }}"
                                        class="form-control form-control-sm adt-select adt-select-narrow"
                                        aria-label="Date from"
                                    >
                                    <button type="submit" class="btn btn-sm ads-btn-primary">
                                        <i data-feather="filter" style="width: 14px; height: 14px;"></i>
                                        Filter
                                    </button>
                                    @if (request()->hasAny(['search', 'status', 'fee_type', 'date_from']))
                                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                                    @endif
                                </form>
                            </div>

                            @if ($transactions->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Reference</th>
                                                <th>Alumni</th>
                                                <th>Fee type</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th class="adt-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($transactions as $transaction)
                                                <tr>
                                                    <td class="fw-medium">{{ $transaction->payment_reference }}</td>
                                                    <td>
                                                        <div class="fw-medium">{{ $transaction->alumni->user->name ?? 'N/A' }}</div>
                                                        <div class="adt-muted small">{{ $transaction->alumni->user->email ?? 'N/A' }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium">{{ $transaction->feeTemplate->feeType->name ?? 'N/A' }}</div>
                                                        <div class="adt-muted small">{{ $transaction->feeTemplate->feeType->code ?? 'N/A' }}</div>
                                                    </td>
                                                    <td class="fw-medium">₦{{ number_format($transaction->amount, 2) }}</td>
                                                    <td>
                                                        @if ($transaction->status === 'paid')
                                                            <span class="adt-status adt-status-active">
                                                                <span class="adt-status-dot"></span>
                                                                Paid
                                                            </span>
                                                        @elseif ($transaction->status === 'pending')
                                                            <span class="adt-status adt-status-pending">
                                                                <span class="adt-status-dot"></span>
                                                                Pending
                                                            </span>
                                                        @else
                                                            <span class="adt-status adt-status-inactive">
                                                                <span class="adt-status-dot"></span>
                                                                Failed
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium">{{ $transaction->created_at->format('M j, Y') }}</div>
                                                        <div class="adt-muted small">{{ $transaction->created_at->format('H:i') }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="adt-actions">
                                                            <a
                                                                href="{{ route('admin.transactions.show', $transaction) }}"
                                                                class="adt-action-btn"
                                                                title="View details"
                                                            >
                                                                <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                                                            </a>
                                                            @if ($transaction->status === 'pending')
                                                                @if ($transaction->payment_provider === 'credocentral')
                                                                    <a
                                                                        href="{{ route('admin.transactions.show', $transaction) }}#reconcile"
                                                                        class="adt-action-btn"
                                                                        title="Reconcile with Credo"
                                                                    >
                                                                        <i data-feather="refresh-cw" style="width: 14px; height: 14px;"></i>
                                                                    </a>
                                                                @endif
                                                                <form
                                                                    action="{{ route('admin.transactions.mark-paid', $transaction) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Mark this transaction as paid?')"
                                                                >
                                                                    @csrf
                                                                    <button type="submit" class="adt-action-btn" title="Mark as paid">
                                                                        <i data-feather="check" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                                <form
                                                                    action="{{ route('admin.transactions.mark-failed', $transaction) }}"
                                                                    method="POST"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Mark this transaction as failed?')"
                                                                >
                                                                    @csrf
                                                                    <button type="submit" class="adt-action-btn adt-action-danger" title="Mark as failed">
                                                                        <i data-feather="x" style="width: 14px; height: 14px;"></i>
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

                                @if ($transactions->hasPages())
                                    <div class="adt-footer">
                                        <span class="adt-footer-count">
                                            {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }}
                                        </span>
                                        <div class="adt-pagination">
                                            {{ $transactions->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="adt-empty">
                                    <div class="adt-empty-icon">
                                        <i data-feather="credit-card" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No transactions found</h3>
                                    <p class="adt-empty-text">Try adjusting your filters or check back after payments are recorded.</p>
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
