<div id="post-{{ $post->id }}" class="post-anchor card w-100 shadow-xss rounded-xxl border-0 p-4 mb-3">
    <div class="card-body p-0 d-flex">
        <figure class="avatar me-3">
            <img src="{{ $post->user->avatar ? asset('storage/' . $post->user->avatar) : asset('/images/user-8.png') }}" alt="{{ $post->user->name }}" class="shadow-sm rounded-circle w45">
        </figure>
        <h4 class="fw-700 text-grey-900 font-xssss mt-1">
            {{ $post->user->name }}
            <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500">{{ $post->created_at->diffForHumans() }}</span>
            @if(isset($post->visibility) && $post->visibility === 'all_alumni')
                <span class="badge bg-primary font-xsssss mt-1">All Alumni</span>
            @elseif(isset($post->visibility))
                <span class="badge bg-secondary font-xsssss mt-1">Connections</span>
            @endif
        </h4>
    </div>

    @if($post->isEventShare() && $post->event)
        <div class="card-body p-0 mb-3">
            <a href="{{ route('alumni.events.show', $post->event) }}" class="text-decoration-none d-block">
            <div class="bg-greylight rounded-xxl p-3">
                <div class="d-flex align-items-start">
                    @if($post->event->date)
                        <div class="bg-primary me-3 p-3 rounded-xxl text-center">
                            <h4 class="fw-700 font-sm ls-3 lh-1 text-white mb-0">
                                <span class="ls-1 d-block font-xsss text-white fw-600">{{ strtoupper($post->event->date->format('M')) }}</span>
                                {{ $post->event->date->format('j') }}
                            </h4>
                        </div>
                    @endif
                    <div>
                        <h5 class="fw-700 text-grey-900 font-xssss mb-1">{{ $post->event->eventname }}</h5>
                        @if($post->event->venue)
                            <p class="font-xsssss text-grey-500 mb-1"><i class="feather-map-pin me-1"></i>{{ $post->event->venue }}</p>
                        @endif
                        @if($post->event->description)
                            <p class="font-xssss text-grey-500 mb-0">{{ \Illuminate\Support\Str::limit($post->event->description, 160) }}</p>
                        @endif
                    </div>
                </div>
                @if($post->event->image)
                    <img src="{{ asset('storage/' . $post->event->image) }}" alt="{{ $post->event->eventname }}" class="img-fluid rounded-xxl mt-3 w-100">
                @endif
            </div>
            </a>
        </div>
    @endif

    @if($post->content)
        <div class="card-body p-0 me-lg-5">
            <p class="fw-500 text-grey-700 lh-26 font-xssss w-100 mb-0">{{ $post->content }}</p>
        </div>
    @endif

    @php
        $imageItems = \App\Support\Social\PostMediaGrid::normalizeItems(
            \App\Support\Social\PostMediaGrid::imageMediaForPost($post),
            $post->user->name."'s post"
        );
        $videoMedia = $post->media->filter(
            fn ($media) => $media->getMediaType() === 'video' && $media->getMediaPath()
        );
    @endphp

    @if(count($imageItems) > 0)
        @include('livewire.social.partials.post-media-grid', [
            'items' => $imageItems,
            'lightboxGroup' => 'post-'.$post->id,
            'caption' => $post->user->name."'s post",
        ])
    @endif

    @if($videoMedia->isNotEmpty())
        <div class="card-body d-block p-0 mt-3">
            <div class="row ps-2 pe-2">
                @foreach($videoMedia as $media)
                    @php $mediaPath = $media->getMediaPath(); @endphp
                    <div class="col-12 p-1">
                        <video width="100%" controls class="rounded-3 w-100">
                            <source src="{{ asset('storage/' . $mediaPath) }}" type="video/mp4">
                        </video>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card-body d-flex p-0 mt-3">
        <button type="button"
                wire:click.prevent="toggleLike({{ $post->id }})"
                wire:loading.attr="disabled"
                wire:target="toggleLike"
                class="social-like-btn d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss me-2 border-0 bg-transparent">
            <i class="feather-thumbs-up text-white {{ $liked ? 'bg-red-gradiant' : 'bg-primary-gradiant' }} me-1 btn-round-xs font-xss"></i>
            {{ $post->likes ?? 0 }} {{ ($post->likes ?? 0) === 1 ? 'Like' : 'Likes' }}
        </button>
        <button type="button" wire:click.prevent="toggleComments({{ $post->id }})" class="d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss border-0 bg-transparent">
            <i class="feather-message-circle text-dark text-grey-900 btn-round-sm font-lg me-1"></i>
            <span class="d-none-xss">{{ $post->comments_count }} Comments</span>
        </button>
    </div>

    @if($openCommentsPostId === $post->id)
        <livewire:social.post-comments :post-id="$post->id" :key="'comments-'.$post->id" />
    @endif
</div>
