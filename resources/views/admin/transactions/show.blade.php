<x-alumniadmin-dashboard title="Transaction Details | FuLafia Alumni">
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Transaction Details</h5>
                                <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary btn-sm">
                                    <i data-feather="arrow-left" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                    Back to Transactions
                                </a>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">Transaction Information</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Reference</label>
                                                            <p class="mb-0">{{ $transaction->reference }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Amount</label>
                                                            <p class="mb-0 h5 text-primary">₦{{ number_format($transaction->amount, 2) }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Status</label>
                                                            <p class="mb-0">
                                                                @if($transaction->status === 'paid')
                                                                    <span class="badge bg-success">Paid</span>
                                                                @elseif($transaction->status === 'pending')
                                                                    <span class="badge bg-warning">Pending</span>
                                                                @else
                                                                    <span class="badge bg-danger">Failed</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Payment Method</label>
                                                            <p class="mb-0">{{ ucfirst($transaction->payment_method) }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Payment Provider</label>
                                                            <p class="mb-0">{{ ucfirst($transaction->payment_provider) }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Test Mode</label>
                                                            <p class="mb-0">
                                                                @if($transaction->is_test_mode)
                                                                    <span class="badge bg-info">Yes</span>
                                                                @else
                                                                    <span class="badge bg-secondary">No</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">Timeline</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Created</label>
                                                    <p class="mb-0">{{ $transaction->created_at->format('M d, Y H:i') }}</p>
                                                </div>
                                                @if($transaction->paid_at)
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Paid</label>
                                                        <p class="mb-0">{{ $transaction->paid_at->format('M d, Y H:i') }}</p>
                                                    </div>
                                                @endif
                                                @if($transaction->failed_at)
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Failed</label>
                                                        <p class="mb-0">{{ $transaction->failed_at->format('M d, Y H:i') }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">Alumni Information</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Name</label>
                                                    <p class="mb-0">{{ $transaction->alumni->user->name ?? 'N/A' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Email</label>
                                                    <p class="mb-0">{{ $transaction->alumni->user->email ?? 'N/A' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Matric Number</label>
                                                    <p class="mb-0">{{ $transaction->alumni->matric_number ?? 'N/A' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Faculty</label>
                                                    <p class="mb-0">{{ $transaction->alumni->faculty ?? 'N/A' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Graduation Year</label>
                                                    <p class="mb-0">{{ $transaction->alumni->year_of_graduation ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">Fee Information</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Fee Type</label>
                                                    <p class="mb-0">{{ $transaction->feeTemplate->feeType->name ?? 'N/A' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Fee Code</label>
                                                    <p class="mb-0">{{ $transaction->feeTemplate->feeType->code ?? 'N/A' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Category</label>
                                                    <p class="mb-0">
                                                        @if($transaction->feeTemplate->category)
                                                            <span class="badge bg-primary">{{ $transaction->feeTemplate->category->name }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Graduation Year</label>
                                                    <p class="mb-0">{{ $transaction->feeTemplate->graduation_year ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($transaction->payment_details)
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">Payment Details</h6>
                                                </div>
                                                <div class="card-body">
                                                    <pre class="bg-light p-3 rounded">{{ json_encode($transaction->payment_details, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($transaction->failure_reason)
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">Failure Reason</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-danger">{{ $transaction->failure_reason }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($transaction->status === 'pending')
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">Actions</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="btn-group">
                                                        <form action="{{ route('admin.transactions.mark-paid', $transaction) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Mark this transaction as paid?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success">
                                                                <i data-feather="check" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                                                Mark as Paid
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.transactions.mark-failed', $transaction) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Mark this transaction as failed?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger">
                                                                <i data-feather="x" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                                                Mark as Failed
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>