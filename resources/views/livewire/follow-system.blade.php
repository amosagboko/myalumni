<div class="container mt-5 pt-5" style="margin-left: 150px; margin-top: 100px !important;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0">Friends</h6>
                    </div>
                    <div class="text-muted small">
                        Logged in as: {{ Auth::user()->name }} ({{ Auth::user()->roles->pluck('name')->implode(', ') }})
                    </div>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex gap-2">
                            <div class="input-group input-group-sm" style="width: 300px;">
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search users...">
                                <span class="input-group-text">
                                    <i class="feather-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 🔍 Search Results -->
                    @if(strlen($search) > 2)
                        @if($users->count())
                            <h4 class="fw-700 mb-3">Search Results:</h4>
                            <div class="row">
                                @foreach($users as $user)
                                    <div class="col-md-3 col-sm-4 pe-2 ps-2" wire:key="user-{{ $user->id }}">
                                        <div class="card border-0 shadow-xss rounded-3 overflow-hidden mb-3">
                                            <div class="card-body w-100 text-center">
                                                <figure class="avatar w65">
                                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $user->name }}" class="rounded-circle shadow-xss">
                                                </figure>
                                                <h4 class="fw-700 font-xsss mt-3 mb-1">{{ $user->name }}</h4>
                                                <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-3">{{ $user->email }}</p>
                                                @php $isFollowing = $this->isFollowing($user->id); @endphp
                                                @if($isFollowing)
                                                    <button class="btn btn-danger font-xsssss fw-700 rounded-xl" wire:click="unfollow({{ $user->id }})">Unfollow</button>
                                                @else
                                                    <button class="btn btn-success font-xsssss fw-700 rounded-xl" wire:click="follow({{ $user->id }})">Follow</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p>No users found.</p>
                        @endif
                    @endif

                    <!-- Tabs for Follow/Following -->
                    <ul class="nav nav-tabs" id="friendTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="following-tab" data-bs-toggle="tab" data-bs-target="#following" type="button" role="tab">
                                Following ({{ $this->followingCount }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="followers-tab" data-bs-toggle="tab" data-bs-target="#followers" type="button" role="tab">
                                Followers ({{ $this->followersCount }})
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="friendTabsContent">
                        <!-- Following Tab -->
                        <div class="tab-pane fade show active" id="following" role="tabpanel">
                            <div class="row ps-2 pe-2">
                                @foreach($following as $user)
                                    <div class="col-md-3 col-sm-4 pe-2 ps-2" wire:key="following-{{ $user->id }}">
                                        <div class="card border-0 shadow-xss rounded-3 overflow-hidden mb-3">
                                            <div class="card-body w-100 text-center">
                                                <figure class="avatar w65">
                                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $user->name }}" class="rounded-circle shadow-xss">
                                                </figure>
                                                <h4 class="fw-700 font-xsss mt-3 mb-1">{{ $user->name }}</h4>
                                                <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-3">{{ $user->email }}</p>
                                                <button class="btn btn-danger font-xsssss fw-700 rounded-xl" wire:click="unfollow({{ $user->id }})">Unfollow</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Followers Tab -->
                        <div class="tab-pane fade" id="followers" role="tabpanel">
                            <div class="row ps-2 pe-2">
                                @foreach($followers as $user)
                                    <div class="col-md-3 col-sm-4 pe-2 ps-2" wire:key="followers-{{ $user->id }}">
                                        <div class="card border-0 shadow-xss rounded-3 overflow-hidden mb-3">
                                            <div class="card-body w-100 text-center">
                                                <figure class="avatar w65">
                                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $user->name }}" class="rounded-circle shadow-xss">
                                                </figure>
                                                <h4 class="fw-700 font-xsss mt-3 mb-1">{{ $user->name }}</h4>
                                                <p class="fw-500 font-xsssss text-grey-500 mt-0 mb-3">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>