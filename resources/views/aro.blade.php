<x-layouts.alumni-relations-officer>
    <div class="container-fluid pt-7 mt-5" style="padding: 50px; padding-top: 50px;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0">Alumni Relations Officer Dashboard</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <!-- Quick Stats -->
                            <div class="col-md-3 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Total Alumni</h6>
                                        <h3 class="mb-0">{{ $totalAlumni }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Active Events</h6>
                                        <h3 class="mb-0">{{ $activeEvents }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Pending Posts</h6>
                                        <h3 class="mb-0">{{ $pendingPosts }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">New Messages</h6>
                                        <h3 class="mb-0">{{ $newMessages }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Events -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0">Upcoming Events</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($upcomingEvents->count() > 0)
                                            <div class="list-group list-group-flush">
                                                @foreach($upcomingEvents as $event)
                                                    <div class="list-group-item">
                                                        <h6 class="mb-1">{{ $event->eventname }}</h6>
                                                        <p class="mb-1 text-muted">
                                                            <i class="feather-calendar me-1"></i> {{ $event->date->format('M d, Y') }}
                                                            <br>
                                                            <i class="feather-map-pin me-1"></i> {{ $event->venue }}
                                                        </p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No upcoming events</p>
                                        @endif
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
                                            <a href="{{ route('alumni-relations-officer.users') }}" class="btn btn-primary btn-sm">
                                                <i class="feather-users me-1"></i> Manage Alumni
                                            </a>
                                            <a href="{{ route('upload.alumni') }}" class="btn btn-success btn-sm">
                                                <i class="feather-upload me-1"></i> Upload Alumni
                                            </a>
                                            <a href="{{ route('create.event.index') }}" class="btn btn-info btn-sm">
                                                <i class="feather-calendar me-1"></i> Create Event
                                            </a>
                                            <a href="{{ route('retrieve.credentials') }}" class="btn btn-warning btn-sm">
                                                <i class="feather-key me-1"></i> Retrieve Credentials
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
</x-layouts.alumni-relations-officer>
