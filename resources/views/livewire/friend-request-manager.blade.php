<div
    class="row feed-body connections-page"
    @if($useBackgroundPoll && $pollInterval > 0)
        wire:poll.visible.{{ $pollInterval }}s="refreshQuietly"
    @endif
    wire:loading.delay.longest.class="social-connections-syncing"
>
    <div class="col-xl-8 col-xxl-9 col-lg-8">
        <div class="card shadow-xss w-100 d-block border-0 p-4 mb-3">
            <div class="card-body p-0">
                <h2 class="fw-700 mb-0 mt-0 font-md text-grey-900">Connections</h2>
                <p class="fw-500 font-xssss text-grey-500 mt-1 mb-0">Find alumni, manage requests, and grow your network.</p>
            </div>

            <div class="connections-page__tabs d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
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

        <div class="card shadow-xss w-100 border-0 p-4 mb-3 connections-page__search-card">
            <label for="connections-search" class="fw-700 font-xssss text-grey-900 mb-2 d-block">Find alumni</label>
            <div class="connections-page__search-box search-form-2">
                <i class="ti-search font-xss" aria-hidden="true"></i>
                <input id="connections-search"
                       type="search"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Name, email, class year, department, or matric number..."
                       autocomplete="off"
                       enterkeyhint="search"
                       class="form-control connections-page__search-input text-grey-900 mb-0 bg-greylight theme-dark-bg border-0">
                @if($search !== '')
                    <button type="button"
                            wire:click="clearSearch"
                            class="connections-page__search-clear btn btn-link p-0 border-0"
                            title="Clear search"
                            aria-label="Clear search">
                        <i class="ti-close font-xss text-grey-500"></i>
                    </button>
                @endif
            </div>
            <p class="font-xsssss text-grey-500 mb-0 mt-2">
                @if(strlen(trim($search)) > 0 && strlen(trim($search)) < 2)
                    Type at least 2 characters to search.
                @else
                    Examples: <span class="text-grey-600">James</span>, <span class="text-grey-600">2018</span>, <span class="text-grey-600">Computer Science</span>
                @endif
            </p>
            @if($searchError)
                <p class="text-danger font-xssss mt-2 mb-0">{{ $searchError }}</p>
            @endif
        </div>

        @if(strlen(trim($search)) >= 2)
            <div class="card shadow-xss border-0 rounded-xxl mb-3 connections-page__search-results">
                <div class="card-body p-4 pb-2 d-flex align-items-center justify-content-between">
                    <h4 class="fw-700 font-xssss text-grey-900 mb-0">
                        Search results
                        @if($searchResults->isNotEmpty())
                            <span class="text-grey-500 fw-500">({{ $searchResults->count() }})</span>
                        @endif
                    </h4>
                    <span class="font-xssss text-grey-500" wire:loading wire:target="search,updatedSearch,searchUsers">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Searching...
                    </span>
                </div>

                <div class="card-body pt-0 px-4 pb-4">
                    @forelse($searchResults as $result)
                        @include('livewire.partials.connection-search-result', ['result' => $result])
                    @empty
                        <div class="text-center py-4" wire:loading.remove wire:target="search,updatedSearch,searchUsers">
                            <i class="feather-search btn-round-lg bg-greylight text-grey-500 font-md mb-3 d-inline-flex align-items-center justify-content-center"></i>
                            <p class="font-xssss text-grey-500 mb-0">No alumni found for &ldquo;{{ $search }}&rdquo;.</p>
                            <p class="font-xsssss text-grey-500 mt-2 mb-0">Try a different name, graduation year, or department.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        @if(strlen(trim($search)) < 2)
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
                            <p class="font-xssss text-grey-500 mb-0">Use the search box above to find classmates and send your first connection request.</p>
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
                                    $subtitle = $receiver->alumni?->year_of_graduation
                                        ? 'Class of ' . $receiver->alumni->year_of_graduation
                                        : '@' . Str::before($receiver->email, '@');
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
                    <li class="mb-2">Search by name, class year, department, or email.</li>
                    <li class="mb-2">Tap a result to view their profile before connecting.</li>
                    <li>Check <strong>Requests</strong> to confirm new connections.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
