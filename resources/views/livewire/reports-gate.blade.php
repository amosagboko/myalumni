<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Reports Access</h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">You need to complete the steps below before accessing your report:</p>

                    <ul class="list-group mb-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Onboarding (Bio-data)</span>
                            @if(!$needsBioData)
                                <span class="badge bg-success">✔ Completed</span>
                            @else
                                <span class="badge bg-danger">✖ Pending</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Payments</span>
                            @if(!$needsPayments)
                                <span class="badge bg-success">✔ Completed</span>
                            @else
                                <span class="badge bg-danger">✖ Pending</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Student Affairs Clearance</span>
                            @if($studentCleared)
                                <span class="badge bg-success">✔ Cleared</span>
                            @else
                                <span class="badge bg-danger">✖ Not Cleared</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Academic Affairs Clearance</span>
                            @if($academicCleared)
                                <span class="badge bg-success">✔ Cleared</span>
                            @else
                                <span class="badge bg-danger">✖ Not Cleared</span>
                            @endif
                        </li>
                    </ul>

                    <div class="d-flex flex-column gap-2">
                        @if($needsBioData)
                            <a href="{{ route('alumni.bio-data') }}" class="btn btn-primary">Complete Bio-data</a>
                        @endif
                        @if($needsPayments)
                            <a href="{{ route('alumni.payments.index') }}" class="btn btn-primary">View & Pay Fees</a>
                        @endif
                        <a href="{{ route('alumni.clearance-status') }}" class="btn btn-outline-secondary">View Clearance Status</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
