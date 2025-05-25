<div class="main-content right-chat-active">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left">
            <div class="middle-wrap">
                <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                    <!-- Search Section -->
                    <div class="card-body p-4">
                        <h5 class="fw-700 mb-0 mt-0 font-md text-grey-900">Find Friends</h5>
                        <p class="fw-500 font-xssss text-grey-500 mt-0 mb-3">Search for other users to connect with.</p>
                        <div class="position-relative">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i data-feather="search" class="text-muted" width="16" height="16"></i>
                                </span>
                                <input type="text" 
                                    wire:model.live.debounce.500ms="search"
                                    placeholder="Search by name or email..."
                                    class="form-control border-start-0"
                                >
                            </div>
                            @if($searchError)
                                <p class="text-danger small mt-2 mb-0">{{ $searchError }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Search Results -->
                @if($search && $users->isNotEmpty())
                    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-700 mb-0 mt-0 font-md text-grey-900">Search Results</h5>
                            <div class="list-group list-group-flush">
                                @foreach($users as $user)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                    @if($user->avatar)
                                                        <img src="{{ asset('storage/' . $user->avatar) }}" 
                                                             alt="{{ $user->name }}" 
                                                             class="rounded-circle"
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        {{ substr($user->name, 0, 1) }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                <p class="text-muted small mb-0">{{ $user->email }}</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                @if($user->request_status === 'accepted')
                                                    <button wire:click="unfriend({{ $user->id }})" 
                                                        class="btn btn-outline-danger btn-sm">
                                                        <i data-feather="user-minus" class="me-1" width="14" height="14"></i>
                                                        Unfriend
                                                    </button>
                                                @elseif($user->request_status === 'pending')
                                                    <span class="badge bg-warning text-dark">
                                                        <i data-feather="clock" class="me-1" width="14" height="14"></i>
                                                        Pending
                                                    </span>
                                                @else
                                                    <button wire:click="sendRequest({{ $user->id }})"
                                                        class="btn btn-primary btn-sm">
                                                        <i data-feather="user-plus" class="me-1" width="14" height="14"></i>
                                                        Add Friend
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Main Content Tabs -->
                <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                    <div class="card-body p-4">
                        <ul class="nav nav-tabs nav-tabs-line" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#friends" 
                                        type="button" 
                                        role="tab">
                                    <i data-feather="users" class="me-1" width="16" height="16"></i>
                                    Friends
                                    @if($friends->isNotEmpty())
                                        <span class="badge bg-primary ms-1">{{ $friends->count() }}</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#received" 
                                        type="button" 
                                        role="tab">
                                    <i data-feather="user-plus" class="me-1" width="16" height="16"></i>
                                    Requests
                                    @if($receivedRequests->isNotEmpty())
                                        <span class="badge bg-warning text-dark ms-1">{{ $receivedRequests->count() }}</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#sent" 
                                        type="button" 
                                        role="tab">
                                    <i data-feather="send" class="me-1" width="16" height="16"></i>
                                    Sent
                                    @if($sentRequests->isNotEmpty())
                                        <span class="badge bg-info ms-1">{{ $sentRequests->count() }}</span>
                                    @endif
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-4">
                            <!-- Friends Tab -->
                            <div class="tab-pane fade show active" id="friends" role="tabpanel">
                                @if($friends->isNotEmpty())
                                    <div class="list-group list-group-flush">
                                        @foreach($friends as $friend)
                                            <div class="list-group-item px-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                            @if($friend->avatar)
                                                                <img src="{{ asset('storage/' . $friend->avatar) }}" 
                                                                     alt="{{ $friend->name }}" 
                                                                     class="rounded-circle"
                                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                            @else
                                                                {{ substr($friend->name, 0, 1) }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">{{ $friend->name }}</h6>
                                                        <p class="text-muted small mb-0">{{ $friend->email }}</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <button wire:click="unfriend({{ $friend->id }})"
                                                            class="btn btn-outline-danger btn-sm">
                                                            <i data-feather="user-minus" class="me-1" width="14" height="14"></i>
                                                            Unfriend
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="mb-3">
                                            <i data-feather="users" class="text-muted" width="48" height="48"></i>
                                        </div>
                                        <p class="text-muted mb-0">You haven't added any friends yet.</p>
                                        <p class="text-muted small">Use the search above to find and connect with other users.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Received Requests Tab -->
                            <div class="tab-pane fade" id="received" role="tabpanel">
                                @if($receivedRequests->isNotEmpty())
                                    <div class="list-group list-group-flush">
                                        @foreach($receivedRequests as $request)
                                            <div class="list-group-item px-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                            @if($request->sender->avatar)
                                                                <img src="{{ asset('storage/' . $request->sender->avatar) }}" 
                                                                     alt="{{ $request->sender->name }}" 
                                                                     class="rounded-circle"
                                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                            @else
                                                                {{ substr($request->sender->name, 0, 1) }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">{{ $request->sender->name }}</h6>
                                                        <p class="text-muted small mb-0">{{ $request->sender->email }}</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="btn-group">
                                                            <button wire:click="acceptRequest({{ $request->sender_id }})"
                                                                class="btn btn-success btn-sm">
                                                                <i data-feather="check" class="me-1" width="14" height="14"></i>
                                                                Accept
                                                            </button>
                                                            <button wire:click="rejectRequest({{ $request->sender_id }})"
                                                                class="btn btn-danger btn-sm">
                                                                <i data-feather="x" class="me-1" width="14" height="14"></i>
                                                                Reject
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="mb-3">
                                            <i data-feather="user-plus" class="text-muted" width="48" height="48"></i>
                                        </div>
                                        <p class="text-muted mb-0">No pending friend requests.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Sent Requests Tab -->
                            <div class="tab-pane fade" id="sent" role="tabpanel">
                                @if($sentRequests->isNotEmpty())
                                    <div class="list-group list-group-flush">
                                        @foreach($sentRequests as $request)
                                            <div class="list-group-item px-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                            @if($request->receiver->avatar)
                                                                <img src="{{ asset('storage/' . $request->receiver->avatar) }}" 
                                                                     alt="{{ $request->receiver->name }}" 
                                                                     class="rounded-circle"
                                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                            @else
                                                                {{ substr($request->receiver->name, 0, 1) }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">{{ $request->receiver->name }}</h6>
                                                        <p class="text-muted small mb-0">{{ $request->receiver->email }}</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <span class="badge bg-warning text-dark">
                                                            <i data-feather="clock" class="me-1" width="14" height="14"></i>
                                                            Pending
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="mb-3">
                                            <i data-feather="send" class="text-muted" width="48" height="48"></i>
                                        </div>
                                        <p class="text-muted mb-0">No sent friend requests.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.avatar {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    padding: 0.75rem 1rem;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    background: none;
}

.nav-tabs .nav-link:hover:not(.active) {
    color: #0d6efd;
    border-bottom: 2px solid #dee2e6;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.middle-wrap {
    padding: 20px;
}

.card {
    border-radius: 10px;
}

.shadow-xs {
    box-shadow: 0 2px 4px rgba(0,0,0,.05);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    // Initialize Feather Icons
    feather.replace();
});
</script>
@endpush