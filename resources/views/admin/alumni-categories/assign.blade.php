<x-alumniadmin-dashboard title="Assign Alumni to Categories | FuLafia Alumni">
    <div class="main-content right-chat-active">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Assign Alumni to Categories</h5>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.alumni-categories.export') }}" class="btn btn-success btn-sm">
                                        <i data-feather="download" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                        Export
                                    </a>
                                    <a href="{{ route('admin.alumni-categories.index') }}" class="btn btn-secondary btn-sm">
                                        <i data-feather="settings" class="btn-round-md me-1" style="width: 14px; height: 14px;"></i>
                                        Manage Categories
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                @livewire('admin.assign-categories')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>
