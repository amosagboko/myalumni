<div class="container mt-5 pt-5" style="margin-left: 150px;">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0">Admin Dashboard</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <!-- Quick Stats -->
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Users</h6>
                                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Active Users</h6>
                                    <h3 class="mb-0">{{ $stats['active'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Suspended Users</h6>
                                    <h3 class="mb-0">{{ $stats['suspended'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">New Users Today</h6>
                                    <h3 class="mb-0">{{ $stats['new_today'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Manage Users</h5>
                                    <p class="card-text">View and manage all system users</p>
                                    <a href="{{ route('admin.users') }}" class="btn btn-primary">Manage Users</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Create User</h5>
                                    <p class="card-text">Add new users to the system</p>
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">Create User</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Upload Alumni</h5>
                                    <p class="card-text">Upload alumni records</p>
                                    <a href="{{ route('upload.alumni') }}" class="btn btn-info">Upload Alumni</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Retrieve Credentials</h5>
                                    <p class="card-text">Get alumni login credentials</p>
                                    <a href="{{ route('retrieve.credentials') }}" class="btn btn-warning">Get Credentials</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 