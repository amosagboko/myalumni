<div
    class="social-feed"
    @if($useBackgroundPoll && $pollInterval > 0)
        wire:poll.visible.{{ $pollInterval }}s="refreshQuietly"
    @endif
>
    @if($useBackgroundPoll && $pollInterval > 0)
        <div
            wire:loading.delay.flex
            wire:target="refreshQuietly"
            class="social-feed-sync-indicator"
            role="status"
            aria-live="polite"
        >
            <span class="social-feed-sync-spinner" aria-hidden="true"></span>
            <span>Syncing…</span>
        </div>
    @endif

    @forelse($posts as $post)
        @include('livewire.social.partials.feed-post', [
            'post' => $post,
            'liked' => $likedPostIds[$post->id] ?? false,
            'openCommentsPostId' => $openCommentsPostId,
        ])
    @empty
        <div class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-3 text-center">
            <p class="font-xssss text-grey-500 mb-0">No posts yet. Be the first to share something with your alumni network.</p>
        </div>
    @endforelse

    @if($posts->hasPages())
        <div class="card w-100 shadow-xss rounded-xxl border-0 p-3 mb-3">
            <p class="font-xssss text-grey-500 text-center mb-2">
                Showing {{ $posts->firstItem() }}–{{ $posts->lastItem() }} of {{ $posts->total() }} posts
            </p>
            {{ $posts->links() }}
        </div>
    @endif
</div>
