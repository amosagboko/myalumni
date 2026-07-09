<div>
    @forelse($posts as $post)
        <livewire:social.post-card :post-id="$post->id" :key="'post-'.$post->id" />
    @empty
        <div class="card w-100 shadow-xss rounded-xxl border-0 p-4 mb-3 text-center">
            <p class="font-xssss text-grey-500 mb-0">No posts yet. Be the first to share something with your alumni network.</p>
        </div>
    @endforelse

    @if($posts->hasPages())
        <div class="card w-100 shadow-xss rounded-xxl border-0 p-3 mb-3">
            {{ $posts->links() }}
        </div>
    @endif
</div>
