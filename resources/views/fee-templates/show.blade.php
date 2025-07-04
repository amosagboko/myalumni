<x-alumniadmin-dashboard>
    <div class="container mt-5 pt-7" style="margin-left: 200px;">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0">Fee Details</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Graduation Year</h6>
                                <p class="mb-0">{{ $fee->graduation_year }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Fee Type</h6>
                                <p class="mb-0">{{ $fee->feeType?->name ?? 'Unknown Fee Type' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Amount</h6>
                                <p class="mb-0">₦{{ number_format($fee->amount, 2) }}</p>
                            </div>
                        </div>

                        @if($fee->name)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted mb-1">Name</h6>
                                    <p class="mb-0">{{ $fee->name }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Valid From</h6>
                                <p class="mb-0">{{ $fee->valid_from->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Valid Until</h6>
                                <p class="mb-0">{{ $fee->valid_until ? $fee->valid_until->format('M d, Y') : 'No expiration' }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Status</h6>
                                <p class="mb-0">
                                    <span class="badge {{ $fee->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $fee->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Validity Status</h6>
                                <p class="mb-0">
                                    @if(!$fee->is_active)
                                        <span class="badge bg-secondary">Inactive</span>
                                    @elseif($fee->valid_from > now())
                                        <span class="badge bg-info">Not Started</span>
                                    @elseif($fee->valid_until && $fee->valid_until < now())
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($fee->description)
                            <div class="mb-4">
                                <h6 class="text-muted mb-1">Description</h6>
                                <p class="mb-0">{{ $fee->description }}</p>
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('fee-templates.index') }}" class="btn btn-light">Back to List</a>
                            @can('edit fee templates')
                                <a href="{{ route('fee-templates.edit', $fee) }}" class="btn btn-primary">Edit Fee Template</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 