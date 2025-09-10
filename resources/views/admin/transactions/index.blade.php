<x-alumniadmin-dashboard title="Transaction Management | FuLafia Alumni">
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Transaction Management</h5>
                                <a href="{{ route('admin.transactions.export') }}" class="btn btn-success btn-sm">
                                    <i data-feather="download" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                    Export
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

                                <!-- Statistics Dashboard -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="card-title">Total Transactions</h6>
                                                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i data-feather="credit-card" style="width: 24px; height: 24px;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="card-title">Paid</h6>
                                                        <h3 class="mb-0">{{ $stats['paid'] }}</h3>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i data-feather="check-circle" style="width: 24px; height: 24px;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="card-title">Pending</h6>
                                                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i data-feather="clock" style="width: 24px; height: 24px;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-danger text-white">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="card-title">Failed</h6>
                                                        <h3 class="mb-0">{{ $stats['failed'] }}</h3>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i data-feather="x-circle" style="width: 24px; height: 24px;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Filters -->
                                <div class="mb-4 p-3 bg-light rounded">
                                    <form method="GET" class="row g-3">
                                        <div class="col-md-2">
                                            <label for="search" class="form-label">Search</label>
                                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                                   placeholder="Reference, Email" 
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="status" class="form-label">Status</label>
                                            <select name="status" id="status" class="form-select">
                                                <option value="">All Status</option>
                                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="fee_type" class="form-label">Fee Type</label>
                                            <select name="fee_type" id="fee_type" class="form-select">
                                                <option value="">All Types</option>
                                                @foreach($feeTypes as $feeType)
                                                    <option value="{{ $feeType->id }}" {{ request('fee_type') == $feeType->id ? 'selected' : '' }}>
                                                        {{ $feeType->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="date_from" class="form-label">Date From</label>
                                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-outline-primary me-2">
                                                <i data-feather="search" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                                Filter
                                            </button>
                                            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                                                <i data-feather="x" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                                Clear
                                            </a>
                                        </div>
                                    </form>
                                </div>

                                <!-- Transactions Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Reference</th>
                                                <th>Alumni</th>
                                                <th>Fee Type</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($transactions as $transaction)
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold">{{ $transaction->reference }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold">{{ $transaction->alumni->user->name ?? 'N/A' }}</div>
                                                        <small class="text-muted">{{ $transaction->alumni->user->email ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold">{{ $transaction->feeTemplate->feeType->name ?? 'N/A' }}</div>
                                                        <small class="text-muted">{{ $transaction->feeTemplate->feeType->code ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold">₦{{ number_format($transaction->amount, 2) }}</span>
                                                    </td>
                                                    <td>
                                                        @if($transaction->status === 'paid')
                                                            <span class="badge bg-success">Paid</span>
                                                        @elseif($transaction->status === 'pending')
                                                            <span class="badge bg-warning">Pending</span>
                                                        @else
                                                            <span class="badge bg-danger">Failed</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold">{{ $transaction->created_at->format('M d, Y') }}</div>
                                                        <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('admin.transactions.show', $transaction) }}" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i data-feather="eye" class="btn-round-md" style="width: 14px; height: 14px;"></i>
                                                            </a>
                                                            @if($transaction->status === 'pending')
                                                                <form action="{{ route('admin.transactions.mark-paid', $transaction) }}" 
                                                                      method="POST" 
                                                                      class="d-inline"
                                                                      onsubmit="return confirm('Mark this transaction as paid?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                                        <i data-feather="check" class="btn-round-md" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                                <form action="{{ route('admin.transactions.mark-failed', $transaction) }}" 
                                                                      method="POST" 
                                                                      class="d-inline"
                                                                      onsubmit="return confirm('Mark this transaction as failed?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                        <i data-feather="x" class="btn-round-md" style="width: 14px; height: 14px;"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">No transactions found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="mt-4">
                                    {{ $transactions->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>