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
        @include('livewire.social.partials.feed-event-share', ['post' => $post])
    @endif

    @if($post->content)
        <div class="card-body p-0 me-lg-5">
            <p class="fw-500 text-grey-700 lh-26 font-xssss w-100 mb-0">{{ $post->content }}</p>
        </div>
    @endif

    @if($post->media->isNotEmpty())
        <div class="card-body d-block p-0 mt-3">
            <div class="row ps-2 pe-2">
                @foreach($post->media as $media)
                    @php
                        $mediaPath = $media->getMediaPath();
                        $mediaType = $media->getMediaType();
                    @endphp
                    @if($mediaType === 'image' && $mediaPath)
                        <div class="col-xs-4 col-sm-4 p-1">
                            <a href="{{ asset('storage/' . $mediaPath) }}" data-lightbox="post-{{ $post->id }}" data-title="{{ $post->user->name }}'s post">
                                <img src="{{ asset('storage/' . $mediaPath) }}" class="rounded-3 w-100" alt="Post image">
                            </a>
                        </div>
                    @elseif($mediaType === 'video' && $mediaPath)
                        <div class="col-12 p-1">
                            <video width="100%" controls class="rounded-3 w-100">
                                <source src="{{ asset('storage/' . $mediaPath) }}" type="video/mp4">
                            </video>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <div class="card-body d-flex p-0 mt-3">
        <button type="button" wire:click.prevent="toggleLike" class="social-like-btn d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss me-2 border-0 bg-transparent">
            <i class="feather-thumbs-up text-white {{ $liked ? 'bg-red-gradiant' : 'bg-primary-gradiant' }} me-1 btn-round-xs font-xss"></i>
            {{ $post->likes ?? 0 }} {{ ($post->likes ?? 0) === 1 ? 'Like' : 'Likes' }}
        </button>
        <button type="button" wire:click.prevent="toggleComments" class="d-flex align-items-center fw-600 text-grey-900 text-dark lh-26 font-xssss border-0 bg-transparent">
            <i class="feather-message-circle text-dark text-grey-900 btn-round-sm font-lg me-1"></i>
            <span class="d-none-xss">{{ $post->comments_count }} Comments</span>
        </button>
    </div>

    @if($showComments)
        <livewire:social.post-comments :post-id="$post->id" :key="'comments-'.$post->id" />
    @endif
</div>
