<x-alumniadmin-dashboard title="Transaction Details | FuLafia Alumni">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-surface admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Transaction details</h1>
                                <p class="ads-page-subtitle">Reference {{ $transaction->payment_reference }}</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Back to transactions
                                </a>
                            </div>
                        </div>

                        @foreach (['success' => 'success', 'error' => 'error', 'info' => 'warning'] as $key => $class)
                            @if (session($key))
                                <div class="ads-alert ads-alert-{{ $class }}">{{ session($key) }}</div>
                            @endif
                        @endforeach

                        <div class="ads-stats">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Amount</span>
                                <span class="ads-stat-value ads-stat-value-sm">₦{{ number_format($transaction->amount, 2) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Status</span>
                                <span class="ads-stat-value ads-stat-value-sm">{{ ucfirst($transaction->status) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Payment method</span>
                                <span class="ads-stat-value ads-stat-value-sm">{{ ucfirst($transaction->payment_method ?? 'N/A') }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Test mode</span>
                                <span class="ads-stat-value ads-stat-value-sm">{{ $transaction->is_test_mode ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">Transaction information</h2>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="small text-muted mb-1">Payment reference</div>
                                            <div class="fw-medium"><code class="small">{{ $transaction->payment_reference }}</code></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted mb-1">Credo reference</div>
                                            <div>
                                                @if ($transaction->payment_provider_reference)
                                                    <code class="small">{{ $transaction->payment_provider_reference }}</code>
                                                @else
                                                    <span class="adt-muted">Not recorded</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted mb-1">Status</div>
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
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted mb-1">Payment provider</div>
                                            <div>{{ ucfirst($transaction->payment_provider ?? 'N/A') }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-muted mb-1">Created</div>
                                            <div>{{ $transaction->created_at->format('M j, Y H:i') }}</div>
                                        </div>
                                        @if ($transaction->paid_at)
                                            <div class="col-md-6">
                                                <div class="small text-muted mb-1">Paid at</div>
                                                <div>{{ $transaction->paid_at->format('M j, Y H:i') }}</div>
                                            </div>
                                        @endif
                                        @if ($transaction->failed_at)
                                            <div class="col-md-6">
                                                <div class="small text-muted mb-1">Failed at</div>
                                                <div>{{ $transaction->failed_at->format('M j, Y H:i') }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">Fee information</h2>
                                    <div class="mb-3">
                                        <div class="small text-muted mb-1">Fee type</div>
                                        <div class="fw-medium">{{ $transaction->feeTemplate->feeType->name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small text-muted mb-1">Fee code</div>
                                        <div><code class="small">{{ $transaction->feeTemplate->feeType->code ?? 'N/A' }}</code></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small text-muted mb-1">Category</div>
                                        @if ($transaction->feeTemplate->category)
                                            <span class="adt-tag">{{ $transaction->feeTemplate->category->name }}</span>
                                        @else
                                            <span class="adt-muted">N/A</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="small text-muted mb-1">Graduation year</div>
                                        <div>{{ $transaction->feeTemplate->graduation_year ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Alumni information</h2>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="small text-muted mb-1">Name</div>
                                        <div class="fw-medium">{{ $transaction->alumni->user->name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="small text-muted mb-1">Email</div>
                                        <div>{{ $transaction->alumni->user->email ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="small text-muted mb-1">Matric number</div>
                                        <div>{{ $transaction->alumni->matric_number ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted mb-1">Faculty</div>
                                        <div>{{ $transaction->alumni->faculty ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted mb-1">Graduation year</div>
                                        <div>{{ $transaction->alumni->year_of_graduation ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($transaction->payment_details)
                            <div class="ads-section">
                                <div class="ads-section-card">
                                    <h2 class="ads-section-title">Payment details</h2>
                                    <pre class="bg-light p-3 rounded small mb-0">{{ json_encode($transaction->payment_details, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        @endif

                        @if ($transaction->failure_reason)
                            <div class="ads-section">
                                <div class="ads-alert ads-alert-error mb-0">
                                    <strong>Failure reason:</strong> {{ $transaction->failure_reason }}
                                </div>
                            </div>
                        @endif

                        @if (in_array($transaction->status, ['pending', 'failed'], true) && $transaction->payment_provider === 'credocentral')
                            <div class="ads-section" id="reconcile">
                                <div class="ads-section-card">
                                    <h2 class="ads-section-title">Reconcile with Credo</h2>
                                    <p class="text-muted small mb-3">
                                        Use this when an alumni paid successfully but the transaction is still stuck as pending.
                                        The system will verify with Credo Central and mark it paid if confirmed.
                                    </p>
                                    <form
                                        action="{{ route('admin.transactions.reconcile', $transaction) }}"
                                        method="POST"
                                        class="row g-3 align-items-end"
                                        onsubmit="return confirm('Verify this payment with Credo Central and update the transaction status?')"
                                    >
                                        @csrf
                                        <div class="col-md-8">
                                            <label for="credo_reference" class="form-label small text-muted mb-1">
                                                Credo transRef
                                                @if ($transaction->payment_provider_reference)
                                                    <span class="text-muted">(optional — stored reference will be used)</span>
                                                @else
                                                    <span class="text-danger">(required)</span>
                                                @endif
                                            </label>
                                            <input
                                                type="text"
                                                name="credo_reference"
                                                id="credo_reference"
                                                value="{{ old('credo_reference', $transaction->payment_provider_reference) }}"
                                                class="form-control form-control-sm @error('credo_reference') is-invalid @enderror"
                                                placeholder="e.g. bNI200QuLL34qft486xE or vs_xxxxxxxxxxxx"
                                                @if (!$transaction->payment_provider_reference) required @endif
                                            >
                                            @error('credo_reference')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                Use the <strong>Credo reference</strong> from the Credo dashboard — not the DUE-/ALUMNI- payment reference.
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-sm ads-btn-primary w-100">
                                                <i data-feather="refresh-cw" style="width: 14px; height: 14px;"></i>
                                                Reconcile payment
                                            </button>
                                        </div>
                                    </form>

                                    @php
                                        $lastVerification = data_get($transaction->payment_details, 'verification_data');
                                    @endphp
                                    @if (is_array($lastVerification))
                                        <div class="mt-3 p-3 bg-light rounded small text-start">
                                            <div class="fw-semibold mb-2">Last Credo verify response</div>
                                            <div>Status: <code>{{ $lastVerification['status'] ?? 'unknown' }}</code></div>
                                            <div>Verified with: <code>{{ $lastVerification['verified_with_reference'] ?? 'n/a' }}</code></div>
                                            <div>Amount match: {{ !empty($lastVerification['amount_matches']) ? 'yes' : 'no' }}</div>
                                            <div>Reference match: {{ !empty($lastVerification['reference_matches']) ? 'yes' : 'no' }}</div>
                                            @if (!empty($lastVerification['returned_amount']))
                                                <div>Credo amount: ₦{{ number_format((float) $lastVerification['returned_amount'], 2) }}</div>
                                            @endif
                                            @if (!empty($lastVerification['business_ref']))
                                                <div>Credo business ref: <code>{{ $lastVerification['business_ref'] }}</code></div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($transaction->status === 'pending')
                            <div class="ads-section">
                                <div class="ads-section-card">
                                    <h2 class="ads-section-title">Actions</h2>
                                    <div class="d-flex flex-wrap gap-2">
                                        <form
                                            action="{{ route('admin.transactions.mark-paid', $transaction) }}"
                                            method="POST"
                                            onsubmit="return confirm('Mark this transaction as paid?')"
                                        >
                                            @csrf
                                            <button type="submit" class="btn btn-sm ads-btn-primary">
                                                <i data-feather="check" style="width: 14px; height: 14px;"></i>
                                                Mark as paid
                                            </button>
                                        </form>
                                        <form
                                            action="{{ route('admin.transactions.mark-failed', $transaction) }}"
                                            method="POST"
                                            onsubmit="return confirm('Mark this transaction as failed?')"
                                        >
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i data-feather="x" style="width: 14px; height: 14px;"></i>
                                                Mark as failed
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

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
