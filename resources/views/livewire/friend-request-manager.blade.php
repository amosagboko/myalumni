<div>
    <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
        <div class="card-body p-4">
            <h4 class="fw-700 mb-0 font-xssss text-grey-900">Find Alumni</h4>
            <p class="fw-500 font-xssss text-grey-500 mt-1 mb-3">Search for other alumni to connect with.</p>
            <div class="form-group icon-input mb-0">
                <i class="feather-search font-sm text-grey-400"></i>
                <input type="text"
                       wire:model.live.debounce.500ms="search"
                       placeholder="Search by name or email..."
                       class="bg-grey text-grey-500 mb-0 theme-color font-xssss fw-500 rounded-3 form-control">
            </div>
            @if($searchError)
                <p class="text-danger font-xssss mt-2 mb-0">{{ $searchError }}</p>
            @endif
        </div>
    </div>

    @if($search && $users->isNotEmpty())
        <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
            <div class="card-body p-4">
                <h4 class="fw-700 mb-3 font-xssss text-grey-900">Search Results</h4>
                @foreach($users as $user)
                    <div class="card-body bg-transparent-card d-flex p-3 bg-greylight rounded-3 mb-2">
                        <figure class="avatar me-2 mb-0">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('/images/user-8.png') }}"
                                 alt="{{ $user->name }}"
                                 class="shadow-sm rounded-circle w45">
                        </figure>
                        <h4 class="fw-700 text-grey-900 font-xssss mt-2">
                            {{ $user->name }}
                            <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">{{ $user->email }}</span>
                        </h4>
                        <div class="ms-auto mt-2">
                            @if($user->request_status === 'accepted')
                                <button type="button"
                                        wire:click="unfriend({{ $user->id }})"
                                        class="p-2 lh-20 bg-grey text-grey-800 font-xssss fw-600 ls-1 rounded-xl border-0">
                                    Remove
                                </button>
                            @elseif($user->request_status === 'pending')
                                <span class="badge bg-warning text-dark font-xssss">Pending</span>
                            @else
                                <button type="button"
                                        wire:click="sendRequest({{ $user->id }})"
                                        class="p-2 lh-20 bg-primary-gradiant text-white font-xssss fw-600 ls-1 rounded-xl border-0">
                                    Connect
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
        <div class="card-body p-4">
            <ul class="nav nav-tabs border-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active font-xssss fw-600 text-grey-500"
                            data-bs-toggle="tab"
                            data-bs-target="#connections"
                            type="button"
                            role="tab">
                        Connections
                        @if($friends->isNotEmpty())
                            <span class="badge bg-primary ms-1">{{ $friends->count() }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link font-xssss fw-600 text-grey-500"
                            data-bs-toggle="tab"
                            data-bs-target="#received"
                            type="button"
                            role="tab">
                        Requests
                        @if($receivedRequests->isNotEmpty())
                            <span class="badge bg-warning text-dark ms-1">{{ $receivedRequests->count() }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link font-xssss fw-600 text-grey-500"
                            data-bs-toggle="tab"
                            data-bs-target="#sent"
                            type="button"
                            role="tab">
                        Sent
                        @if($sentRequests->isNotEmpty())
                            <span class="badge bg-info ms-1">{{ $sentRequests->count() }}</span>
                        @endif
                    </button>
                </li>
            </ul>

            <div class="tab-content pt-4">
                <div class="tab-pane fade show active" id="connections" role="tabpanel">
                    @if($friends->isNotEmpty())
                        @foreach($friends as $friend)
                            <div class="card-body bg-transparent-card d-flex p-3 bg-greylight rounded-3 mb-2">
                                <figure class="avatar me-2 mb-0">
                                    <img src="{{ $friend->avatar ? asset('storage/' . $friend->avatar) : asset('/images/user-8.png') }}"
                                         alt="{{ $friend->name }}"
                                         class="shadow-sm rounded-circle w45">
                                </figure>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-2">
                                    {{ $friend->name }}
                                    <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">{{ $friend->email }}</span>
                                </h4>
                                <button type="button"
                                        wire:click="unfriend({{ $friend->id }})"
                                        class="p-2 lh-20 bg-grey text-grey-800 font-xssss fw-600 ls-1 rounded-xl border-0 ms-auto mt-2">
                                    Remove
                                </button>
                            </div>
                        @endforeach
                    @else
                        <p class="font-xssss text-grey-500 text-center py-4 mb-0">You have no connections yet. Search above to find alumni.</p>
                    @endif
                </div>

                <div class="tab-pane fade" id="received" role="tabpanel">
                    @if($receivedRequests->isNotEmpty())
                        @foreach($receivedRequests as $request)
                            <div class="card-body d-flex pt-0 ps-0 pe-0 pb-0 border-0">
                                <figure class="avatar me-3">
                                    <img src="{{ $request->sender->avatar ? asset('storage/' . $request->sender->avatar) : asset('/images/user-8.png') }}"
                                         alt="{{ $request->sender->name }}"
                                         class="shadow-sm rounded-circle w45">
                                </figure>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-1">
                                    {{ $request->sender->name }}
                                    <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">{{ $request->sender->email }}</span>
                                </h4>
                            </div>
                            <div class="card-body d-flex align-items-center pt-2 ps-0 pe-0 pb-3">
                                <button type="button"
                                        wire:click="acceptRequest({{ $request->sender_id }})"
                                        class="p-2 lh-20 w100 bg-primary-gradiant me-2 text-white text-center font-xssss fw-600 ls-1 rounded-xl border-0">
                                    Confirm
                                </button>
                                <button type="button"
                                        wire:click="rejectRequest({{ $request->sender_id }})"
                                        class="p-2 lh-20 w100 bg-grey text-grey-800 text-center font-xssss fw-600 ls-1 rounded-xl border-0">
                                    Delete
                                </button>
                            </div>
                        @endforeach
                    @else
                        <p class="font-xssss text-grey-500 text-center py-4 mb-0">No pending connection requests.</p>
                    @endif
                </div>

                <div class="tab-pane fade" id="sent" role="tabpanel">
                    @if($sentRequests->isNotEmpty())
                        @foreach($sentRequests as $request)
                            <div class="card-body bg-transparent-card d-flex p-3 bg-greylight rounded-3 mb-2">
                                <figure class="avatar me-2 mb-0">
                                    <img src="{{ $request->receiver->avatar ? asset('storage/' . $request->receiver->avatar) : asset('/images/user-8.png') }}"
                                         alt="{{ $request->receiver->name }}"
                                         class="shadow-sm rounded-circle w45">
                                </figure>
                                <h4 class="fw-700 text-grey-900 font-xssss mt-2">
                                    {{ $request->receiver->name }}
                                    <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">{{ $request->receiver->email }}</span>
                                </h4>
                                <span class="badge bg-warning text-dark font-xssss ms-auto mt-2">Pending</span>
                            </div>
                        @endforeach
                    @else
                        <p class="font-xssss text-grey-500 text-center py-4 mb-0">No sent connection requests.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
