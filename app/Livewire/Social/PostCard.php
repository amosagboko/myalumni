<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use App\Models\Post;
use App\Services\Social\FeedService;
use App\Services\Social\PostService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class PostCard extends Component
{
    use ListensForSocialBroadcasts;

    public int $postId;
    public bool $showComments = false;

    public function getListeners(): array
    {
        return array_merge(
            ['comment-added' => 'onCommentAdded'],
            $this->backgroundFeedListener()
        );
    }

    public function onCommentAdded(?int $postId = null): void
    {
        if ($postId !== null && (int) $postId !== $this->postId) {
            return;
        }
    }

    protected function shouldRefreshFromSocialBroadcast(?array $payload): bool
    {
        if (! is_array($payload) || ! isset($payload['postId'])) {
            return true;
        }

        return (int) $payload['postId'] === $this->postId;
    }

    public function mount(int $postId): void
    {
        $this->postId = $postId;
    }

    public function toggleLike(PostService $postService, FeedService $feedService): void
    {
        $post = Post::findOrFail($this->postId);

        if (!$feedService->canViewPost($post, Auth::user())) {
            session()->flash('error', 'You cannot like this post.');
            return;
        }

        try {
            $postService->toggleLike($post, Auth::user());
            $this->dispatch('notifications-updated');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function toggleComments(): void
    {
        $this->showComments = !$this->showComments;
    }

    public function render(FeedService $feedService, PostService $postService)
    {
        $query = Post::with(['user', 'media'])->withCount('comments');

        if (Schema::hasColumn('posts', 'event_id')) {
            $query->with('event');
        }

        $post = $query->findOrFail($this->postId);

        if (!$feedService->canViewPost($post, Auth::user())) {
            return view('livewire.social.post-card-hidden');
        }

        return view('livewire.social.post-card', [
            'post' => $post,
            'liked' => $postService->userHasLiked($post, Auth::user()),
        ]);
    }
}
