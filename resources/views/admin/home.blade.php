<x-alumniadmin-dashboard>
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
                                        <h3 class="mb-0">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Active Users</h6>
                                        <h3 class="mb-0">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Suspended Users</h6>
                                        <h3 class="mb-0">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">New Registrations</h6>
                                        <h3 class="mb-0">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Quick Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.users') }}" class="btn btn-primary btn-sm">
                                                <i class="feather-users me-1"></i> Manage Users
                                            </a>
                                            <a href="{{ route('admin.users.create') }}" class="btn btn-success btn-sm">
                                                <i class="feather-user-plus me-1"></i> Create User
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 