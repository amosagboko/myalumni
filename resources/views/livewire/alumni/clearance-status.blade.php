<div class="clearance-status-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="fw-600 mb-1">Clearance Status</h4>
                <p class="text-grey-500 font-xssss mb-0">
                    Track your portal requirements and university division clearance.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if ($canAccessClearanceForm)
                    <a href="{{ route('reports') }}" class="btn btn-outline-primary btn-sm">
                        <i class="feather-file-text me-1"></i> View Clearance Form
                    </a>
                @endif
                <a href="{{ route('alumni.home') }}" class="btn btn-outline-secondary btn-sm">
                    Dashboard
                </a>
            </div>
        </div>

        <div class="card-body p-4 w-100 border-0">
            @if (! $hasAlumniRecord)
                <div class="alert alert-warning mb-0">
                    <strong>No alumni record found.</strong>
                    <p class="mb-0 mt-2 font-xssss">{{ $overall['message'] }}</p>
                    <a href="{{ route('alumni.bio-data') }}" class="btn btn-primary btn-sm mt-3">Complete Bio-data</a>
                </div>
            @else
                <div class="border rounded-3 p-3 mb-4 bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="text-muted font-xsssss text-uppercase fw-600">Overall status</div>
                        <div class="font-xssss text-grey-700 mt-1">{{ $overall['message'] }}</div>
                    </div>
                    <span class="badge {{ $overall['badgeClass'] }} font-xssss">{{ $overall['label'] }}</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted font-xsssss text-uppercase fw-600">Alumni name</div>
                            <div class="font-xssss fw-600 mt-1">{{ $user->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted font-xsssss text-uppercase fw-600">Matriculation number</div>
                            <div class="font-xssss fw-600 mt-1">{{ $alumni->matric_number ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted font-xsssss text-uppercase fw-600">Year of graduation</div>
                            <div class="font-xssss fw-600 mt-1">{{ $alumni->year_of_graduation ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <h6 class="fw-600 font-xssss text-grey-900 mb-3">Portal requirements</h6>
                <ul class="list-group mb-4">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Onboarding (Bio-data)</span>
                        @if (! $portal['needsBioData'])
                            <span class="badge bg-success">Completed</span>
                        @else
                            <span class="badge bg-danger">Pending</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Payments</span>
                        @if (! $portal['needsPayments'])
                            <span class="badge bg-success">Completed</span>
                        @else
                            <span class="badge bg-danger">Pending</span>
                        @endif
                    </li>
                </ul>

                @if ($requiresDivisionClearance)
                    <h6 class="fw-600 font-xssss text-grey-900 mb-3">University division clearance</h6>
                    <ul class="list-group mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Student Affairs Division</span>
                            @if ($studentAffairsCleared)
                                <span class="badge bg-success">Cleared</span>
                            @else
                                <span class="badge bg-danger">Not cleared</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Academic Affairs Division</span>
                            @if ($academicAffairsCleared)
                                <span class="badge bg-success">Cleared</span>
                            @else
                                <span class="badge bg-danger">Not cleared</span>
                            @endif
                        </li>
                    </ul>
                @else
                    <div class="alert alert-info font-xssss mb-4">
                        Division clearance is not required for your graduation year
                        ({{ $alumni->year_of_graduation ?? 'N/A' }}).
                        It applies to alumni graduating in {{ $divisionClearanceFromYear }} or later.
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    @if ($portal['needsBioData'])
                        <a href="{{ route('alumni.bio-data') }}" class="btn btn-primary btn-sm">Complete Bio-data</a>
                    @endif
                    @if ($portal['needsPayments'])
                        <a href="{{ route('alumni.payments.index') }}" class="btn btn-primary btn-sm">View &amp; Pay Fees</a>
                    @endif
                    @if ($canAccessClearanceForm)
                        <a href="{{ route('reports') }}" class="btn btn-outline-primary btn-sm">Open Clearance Form</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
