<div class="row feed-body connections-page">
    <div class="col-xl-8 col-xxl-9 col-lg-8">
        <div class="card shadow-xss w-100 d-block border-0 p-4 mb-3">
            <div class="card-body d-flex flex-wrap align-items-center p-0 gap-3">
                <div>
                    <h2 class="fw-700 mb-0 mt-0 font-md text-grey-900">Connections</h2>
                    <p class="fw-500 font-xssss text-grey-500 mt-1 mb-0">Find alumni, manage requests, and grow your network.</p>
                </div>
                <div class="search-form-2 ms-lg-auto flex-grow-1 connections-page__search">
                    <i class="ti-search font-xss"></i>
                    <input type="text"
                           wire:model.live.debounce.500ms="search"
                           placeholder="Search by name or email..."
                           class="form-control text-grey-500 mb-0 bg-greylight theme-dark-bg border-0">
                </div>
            </div>

            @if($searchError)
                <p class="text-danger font-xssss mt-3 mb-0">{{ $searchError }}</p>
            @endif

            <div class="connections-page__tabs d-flex flex-wrap gap-2 mt-4 pt-2 border-top">
                <button type="button"
                        wire:click="setActiveTab('connections')"
                        class="btn btn-sm rounded-xl font-xssss fw-600 {{ $activeTab === 'connections' ? 'bg-primary-gradiant text-white' : 'bg-greylight text-grey-700' }}">
                    My Connections
                    @if($friends->isNotEmpty())
                        <span class="badge {{ $activeTab === 'connections' ? 'bg-white text-primary' : 'bg-primary text-white' }} ms-1">{{ $friends->count() }}</span>
                    @endif
                </button>
                <button type="button"
                        wire:click="setActiveTab('received')"
                        class="btn btn-sm rounded-xl font-xssss fw-600 {{ $activeTab === 'received' ? 'bg-primary-gradiant text-white' : 'bg-greylight text-grey-700' }}">
                    Requests
                    @if($receivedRequests->isNotEmpty())
                        <span class="badge {{ $activeTab === 'received' ? 'bg-white text-primary' : 'bg-warning text-dark' }} ms-1">{{ $receivedRequests->count() }}</span>
                    @endif
                </button>
                <button type="button"
                        wire:click="setActiveTab('sent')"
                        class="btn btn-sm rounded-xl font-xssss fw-600 {{ $activeTab === 'sent' ? 'bg-primary-gradiant text-white' : 'bg-greylight text-grey-700' }}">
                    Sent
                    @if($sentRequests->isNotEmpty())
                        <span class="badge {{ $activeTab === 'sent' ? 'bg-white text-primary' : 'bg-info text-white' }} ms-1">{{ $sentRequests->count() }}</span>
                    @endif
                </button>
            </div>
        </div>

        @if($search && strlen($search) >= 2)
            <div class="mb-2 ps-2 pe-2 d-flex align-items-center justify-content-between">
                <h4 class="fw-700 font-xssss text-grey-900 mb-0">Search Results</h4>
                <span class="font-xssss text-grey-500" wire:loading wire:target="search,updatedSearch,searchUsers">Searching...</span>
            </div>

            @if($users->isNotEmpty())
                <div class="row ps-2 pe-2">
                    @foreach($users as $user)
                        @php
                            $avatarUrl = $user->avatar ? '/storage/' . ltrim($user->avatar, '/') : '/images/user-8.png';
                            $subtitle = '@' . Str::before($user->email, '@');
                        @endphp
                        @include('livewire.partials.connection-member-card', [
                            'name' => $user->name,
                            'subtitle' => $subtitle,
                            'avatarUrl' => $avatarUrl,
                            'mode' => $user->request_status ?? 'none',
                            'userId' => $user->id,
                        ])
                    @endforeach
                </div>
            @elseif(! $isSearching)
                <div class="card shadow-xss border-0 rounded-xxl mb-3">
                    <div class="card-body p-4 text-center">
                        <p class="font-xssss text-grey-500 mb-0">No alumni found for "{{ $search }}".</p>
                    </div>
                </div>
            @endif
        @endif

        @if(! $search || strlen($search) < 2)
            @if($activeTab === 'connections')
                @if($friends->isNotEmpty())
                    <div class="row ps-2 pe-2">
                        @foreach($friends as $friend)
                            @php
                                $avatarUrl = $friend->avatar ? '/storage/' . ltrim($friend->avatar, '/') : '/images/user-8.png';
                                $subtitle = $friend->alumni?->year_of_graduation
                                    ? 'Class of ' . $friend->alumni->year_of_graduation
                                    : '@' . Str::before($friend->email, '@');
                            @endphp
                            @include('livewire.partials.connection-member-card', [
                                'name' => $friend->name,
                                'subtitle' => $subtitle,
                                'avatarUrl' => $avatarUrl,
                                'mode' => 'accepted',
                                'userId' => $friend->id,
                            ])
                        @endforeach
                    </div>
                @else
                    <div class="card shadow-xss border-0 rounded-xxl mb-3">
                        <div class="card-body p-5 text-center">
                            <i class="feather-users btn-round-lg bg-greylight text-grey-500 font-md mb-3 d-inline-flex align-items-center justify-content-center"></i>
                            <h4 class="fw-700 font-xssss text-grey-900 mb-2">No connections yet</h4>
                            <p class="font-xssss text-grey-500 mb-0">Search above to find alumni and send your first connection request.</p>
                        </div>
                    </div>
                @endif
            @endif

            @if($activeTab === 'received')
                @if($receivedRequests->isNotEmpty())
                    <div class="row ps-2 pe-2">
                        @foreach($receivedRequests as $request)
                            @if($request->sender)
                                @php
                                    $sender = $request->sender;
                                    $avatarUrl = $sender->avatar ? '/storage/' . ltrim($sender->avatar, '/') : '/images/user-8.png';
                                    $subtitle = ($request->mutual_count ?? 0) > 0
                                        ? $request->mutual_count . ' mutual ' . Str::plural('connection', $request->mutual_count)
                                        : 'Wants to connect';
                                @endphp
                                @include('livewire.partials.connection-member-card', [
                                    'name' => $sender->name,
                                    'subtitle' => $subtitle,
                                    'avatarUrl' => $avatarUrl,
                                    'mode' => 'received',
                                    'userId' => $sender->id,
                                ])
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="card shadow-xss border-0 rounded-xxl mb-3">
                        <div class="card-body p-5 text-center">
                            <i class="feather-user-plus btn-round-lg bg-greylight text-grey-500 font-md mb-3 d-inline-flex align-items-center justify-content-center"></i>
                            <h4 class="fw-700 font-xssss text-grey-900 mb-2">No pending requests</h4>
                            <p class="font-xssss text-grey-500 mb-0">When someone sends you a connection request, it will appear here.</p>
                        </div>
                    </div>
                @endif
            @endif

            @if($activeTab === 'sent')
                @if($sentRequests->isNotEmpty())
                    <div class="row ps-2 pe-2">
                        @foreach($sentRequests as $request)
                            @if($request->receiver)
                                @php
                                    $receiver = $request->receiver;
                                    $avatarUrl = $receiver->avatar ? '/storage/' . ltrim($receiver->avatar, '/') : '/images/user-8.png';
                                    $subtitle = '@' . Str::before($receiver->email, '@');
                                @endphp
                                @include('livewire.partials.connection-member-card', [
                                    'name' => $receiver->name,
                                    'subtitle' => $subtitle,
                                    'avatarUrl' => $avatarUrl,
                                    'mode' => 'pending',
                                    'userId' => $receiver->id,
                                ])
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="card shadow-xss border-0 rounded-xxl mb-3">
                        <div class="card-body p-5 text-center">
                            <i class="feather-send btn-round-lg bg-greylight text-grey-500 font-md mb-3 d-inline-flex align-items-center justify-content-center"></i>
                            <h4 class="fw-700 font-xssss text-grey-900 mb-2">No sent requests</h4>
                            <p class="font-xssss text-grey-500 mb-0">Connection requests you send will show up here while they are pending.</p>
                        </div>
                    </div>
                @endif
            @endif
        @endif
    </div>

    <div class="col-xl-4 col-xxl-3 col-lg-4 ps-lg-0">
        <livewire:social.suggested-connections :key="'friends-suggestions-'.auth()->id()" />

        <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
            <div class="card-body p-4">
                <h4 class="fw-700 mb-2 font-xssss text-grey-900">Quick tips</h4>
                <ul class="font-xssss text-grey-500 mb-0 ps-3">
                    <li class="mb-2">Search by name or email to find alumni.</li>
                    <li class="mb-2">Connections can see your <strong>Connections-only</strong> posts.</li>
                    <li>Check <strong>Requests</strong> to confirm new connections.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
