<div
    class="row feed-body alumni-profile-page"
    @if($useBackgroundPoll && $pollInterval > 0)
        wire:poll.visible.{{ $pollInterval }}s="refreshQuietly"
    @endif
    wire:loading.delay.longest.class="social-connections-syncing"
>
    <div class="col-xl-12">
        <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3 mt-3 alumni-profile-page__hero-card">
            <div class="card-body position-relative alumni-profile-page__cover bg-primary-gradiant rounded-top-xxl overflow-hidden"></div>
            <div class="card-body d-block pt-4 pb-3 text-center position-relative alumni-profile-page__hero-body">
                <figure class="avatar mt--6 position-relative w100 z-index-1 ms-auto me-auto alumni-profile-page__avatar">
                    <img src="{{ $avatarUrl }}"
                         alt="{{ $profileUser->name }}"
                         class="p-1 bg-white rounded-xl w-100 shadow-xss">
                </figure>

                <h4 class="font-xs ls-1 fw-700 text-grey-900 mb-0">
                    {{ $profileUser->name }}
                    <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">{{ $subtitle }}</span>
                </h4>

                <div class="d-flex justify-content-center gap-4 mt-3 mb-1 alumni-profile-page__stats-inline">
                    <div class="text-center">
                        <div class="fw-700 font-sm text-grey-900">{{ number_format($postsCount) }}</div>
                        <div class="font-xsssss text-grey-500">{{ Str::plural('Post', $postsCount) }}</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-700 font-sm text-grey-900">{{ number_format($connectionCount) }}</div>
                        <div class="font-xsssss text-grey-500">{{ Str::plural('Connection', $connectionCount) }}</div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-center mt-3 alumni-profile-page__actions">
                    @if($isSelf)
                        <a href="{{ route('profile.edit') }}"
                           class="btn pt-2 pb-2 ps-4 pe-4 rounded-xl bg-primary-gradiant font-xsssss fw-700 text-white border-0 text-uppercase ls-3">
                            Edit Settings
                        </a>
                    @else
                        @include('livewire.partials.connection-member-actions', [
                            'mode' => $connectionMode,
                            'userId' => $profileUser->id,
                            'style' => 'hero',
                        ])
                    @endif
                </div>
            </div>

            <div class="card-body d-block w-100 shadow-none mb-0 p-0 border-top-xs">
                <ul class="nav nav-tabs h55 d-flex product-info-tab border-bottom-0 ps-4" role="tablist">
                    <li class="active list-inline-item me-5">
                        <span class="fw-700 font-xssss text-grey-900 pt-3 pb-3 ls-1 d-inline-block active">About &amp; Posts</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-xxl-3 col-lg-4 pe-0">
        <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
            <div class="card-body d-block p-4">
                <h4 class="fw-700 mb-3 font-xsss text-grey-900">About</h4>
                <p class="fw-500 text-grey-500 lh-24 font-xssss mb-0">
                    @if($profileUser->alumni?->year_of_graduation)
                        Class of {{ $profileUser->alumni->year_of_graduation }}
                    @else
                        Alumni member
                    @endif
                </p>
            </div>

            @if($profileUser->alumni?->faculty)
                <div class="card-body d-flex pt-0">
                    <i class="feather-book text-grey-500 me-3 font-lg"></i>
                    <h4 class="fw-700 text-grey-900 font-xssss mt-0">
                        {{ $profileUser->alumni->faculty }}
                        <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">Faculty</span>
                    </h4>
                </div>
            @endif

            @if($profileUser->alumni?->year_of_graduation)
                <div class="card-body d-flex pt-0">
                    <i class="feather-award text-grey-500 me-3 font-lg"></i>
                    <h4 class="fw-700 text-grey-900 font-xssss mt-0">
                        {{ $profileUser->alumni->year_of_graduation }}
                        <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">Year of graduation</span>
                    </h4>
                </div>
            @endif
        </div>

        @unless($isSelf)
            <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
                <div class="card-body p-4">
                    <h4 class="fw-700 mb-2 font-xssss text-grey-900">Connect</h4>
                    <p class="font-xssss text-grey-500 mb-3">
                        Send a connection request to see {{ $profileUser->name }}&rsquo;s connections-only posts.
                    </p>
                    @include('livewire.partials.connection-member-actions', [
                        'mode' => $connectionMode,
                        'userId' => $profileUser->id,
                    ])
                </div>
            </div>
        @endunless
    </div>

    <div class="col-xl-8 col-xxl-9 col-lg-8">
        @if($posts->isNotEmpty())
            @foreach($posts as $post)
                <livewire:social.post-card :post-id="$post->id" :key="'profile-post-'.$post->id" />
            @endforeach

            <div class="card shadow-xss border-0 rounded-xxl mb-3">
                <div class="card-body p-3">
                    {{ $posts->links() }}
                </div>
            </div>
        @else
            <div class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-3">
                <div class="card-body p-4 text-center">
                    <i class="feather-file-text btn-round-lg bg-greylight text-grey-500 font-md mb-3 d-inline-flex align-items-center justify-content-center"></i>
                    <h4 class="fw-700 font-xssss text-grey-900 mb-2">No posts to show</h4>
                    <p class="font-xssss text-grey-500 mb-0">
                        @if($isSelf)
                            Posts you share with alumni will appear here.
                        @else
                            {{ $profileUser->name }} hasn&rsquo;t shared any posts visible to you yet.
                        @endif
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
