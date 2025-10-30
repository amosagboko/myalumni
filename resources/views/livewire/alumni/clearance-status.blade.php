<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Clearance Status</h6>
                </div>
                <div class="card-body">
                    @if(!$alumni)
                        <div class="alert alert-warning mb-0">No alumni record found.</div>
                    @else
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <div><strong>Alumni Name:</strong></div>
                                <div>{{ $alumni->user->name ?? 'N/A' }}</div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div><strong>Matriculation Number:</strong></div>
                                <div>{{ $alumni->matric_number ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div><strong>Student Affairs Division Status:</strong></div>
                                <div>
                                    @if($alumni->student_affairs_cleared)
                                        <span class="badge bg-success">✔ Cleared</span>
                                    @else
                                        <span class="badge bg-danger">✖ Not Cleared</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div><strong>Academic Affairs Division Status:</strong></div>
                                <div>
                                    @if($alumni->academic_affairs_cleared)
                                        <span class="badge bg-success">✔ Cleared</span>
                                    @else
                                        <span class="badge bg-danger">✖ Not Cleared</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div><strong>Remarks:</strong></div>
                            <div>
                                @if($alumni->student_affairs_cleared && $alumni->academic_affairs_cleared)
                                    <span class="badge bg-primary">Cleared</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
