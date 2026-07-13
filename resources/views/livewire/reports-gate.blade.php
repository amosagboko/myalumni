<div class="clearance-form-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h4 class="fw-600 mb-1">Clearance Form</h4>
            <p class="text-grey-500 font-xssss mb-0">
                Complete the steps below to unlock your clearance form.
            </p>
        </div>

        <div class="card-body p-4 w-100 border-0">
            <ul class="list-group mb-4">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Onboarding (Bio-data)</span>
                    @if (! $needsBioData)
                        <span class="badge bg-success">Completed</span>
                    @else
                        <span class="badge bg-danger">Pending</span>
                    @endif
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Payments</span>
                    @if (! $needsPayments)
                        <span class="badge bg-success">Completed</span>
                    @else
                        <span class="badge bg-danger">Pending</span>
                    @endif
                </li>
            </ul>

            <div class="d-flex flex-column flex-sm-row gap-2">
                @if ($needsBioData)
                    <a href="{{ route('alumni.bio-data') }}" class="btn btn-primary">Complete Bio-data</a>
                @endif
                @if ($needsPayments)
                    <a href="{{ route('alumni.payments.index') }}" class="btn btn-primary">View &amp; Pay Fees</a>
                @endif
                <a href="{{ route('alumni.home') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>
