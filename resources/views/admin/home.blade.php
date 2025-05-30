<x-alumniadmin-dashboard>
    <div class="container mt-5 pt-5" style="margin-left: 150px;">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0">Admin Dashboard</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <!-- User Statistics -->
                            <div class="col-md-3 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Total Users</h6>
                                        <h3 class="mb-0">{{ number_format($totalUsers) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Active Users</h6>
                                        <h3 class="mb-0">{{ number_format($activeUsers) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Suspended Users</h6>
                                        <h3 class="mb-0">{{ number_format($suspendedUsers) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Total Alumni</h6>
                                        <h3 class="mb-0">{{ number_format($totalAlumni) }}</h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Statistics -->
                            <div class="col-md-3 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Total Transactions</h6>
                                        <h3 class="mb-0">{{ number_format($paymentStats['total_transactions']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Paid Transactions</h6>
                                        <h3 class="mb-0">{{ number_format($paymentStats['paid_transactions']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Pending Transactions</h6>
                                        <h3 class="mb-0">{{ number_format($paymentStats['pending_transactions']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Total Amount Paid</h6>
                                        <h3 class="mb-0">₦{{ number_format($paymentStats['total_amount'], 2) }}</h3>
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