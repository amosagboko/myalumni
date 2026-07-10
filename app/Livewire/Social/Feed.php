<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use App\Models\Post;
use App\Services\Social\FeedService;
use App\Services\Social\PostService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Feed extends Component
{
    use ListensForSocialBroadcasts;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?int $openCommentsPostId = null;

    public function getListeners(): array
    {
        return array_merge([
            'post-created' => 'onPostCreated',
            'comment-added' => 'onCommentAdded',
        ], $this->backgroundFeedListener());
    }

    public function onPostCreated(): void
    {
        $this->resetPage();
    }

    public function onCommentAdded(?int $postId = null): void
    {
        if ($postId !== null && $this->openCommentsPostId !== null && $this->openCommentsPostId !== $postId) {
            return;
        }
    }

    public function refreshQuietly(): void
    {
        // Livewire re-renders after polling; no extra work needed.
    }

    public function onBackgroundFeedSync(?array $payload = null): void
    {
        if (! $this->shouldRefreshFromSocialBroadcast($payload)) {
            return;
        }

        if ($this->isOwnBroadcastAction($payload)) {
            return;
        }

        $type = is_array($payload) ? ($payload['type'] ?? null) : null;

        if ($type === 'post.created') {
            $this->resetPage();
        }
    }

    public function toggleLike(int $postId, PostService $postService, FeedService $feedService): void
    {
        $post = Post::findOrFail($postId);

        if (! $feedService->canViewPost($post, Auth::user())) {
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

    public function toggleComments(int $postId): void
    {
        $this->openCommentsPostId = $this->openCommentsPostId === $postId ? null : $postId;
    }

    public function render(FeedService $feedService, PostService $postService)
    {
        $user = Auth::user();
        $posts = $feedService->paginateFeed($user);
        $likedPostIds = [];

        foreach ($posts as $post) {
            $likedPostIds[$post->id] = $postService->userHasLiked($post, $user);
        }

        return view('livewire.social.feed', [
            'posts' => $posts,
            'likedPostIds' => $likedPostIds,
            'useBackgroundPoll' => ! $this->broadcastingEnabled(),
            'pollInterval' => config('social.poll_interval_seconds', 10),
        ]);
    }
}
