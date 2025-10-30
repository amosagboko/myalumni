<div>
    <div class="card">
        <div class="card-header bg-white">
            <h6 class="mb-0">Clearance Status</h6>
        </div>
        <div class="card-body">
            @if(!$alumni)
                <div class="alert alert-warning mb-0">No alumni record found.</div>
            @else
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Alumni Name:</strong> {{ $alumni->user->name ?? 'N/A' }}</div>
                    <div class="col-md-6"><strong>Matriculation Number:</strong> {{ $alumni->matric_number ?? 'N/A' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Student Affairs Division Status:</strong>
                        @if($alumni->student_affairs_cleared)
                            <span class="text-success">✔</span>
                        @else
                            <span class="text-danger">✖</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Academic Affairs Division Status:</strong>
                        @if($alumni->academic_affairs_cleared)
                            <span class="text-success">✔</span>
                        @else
                            <span class="text-danger">✖</span>
                        @endif
                    </div>
                </div>
                <div>
                    <strong>Remarks:</strong>
                    @if($alumni->student_affairs_cleared && $alumni->academic_affairs_cleared)
                        Cleared
                    @else
                        Pending
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
