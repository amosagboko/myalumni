<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use App\Models\Post;
use App\Support\Social\CommentTreeBuilder;
use App\Support\Social\EmojiPicker;
use App\Services\Social\FeedService;
use App\Services\Social\PostService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PostComments extends Component
{
    use ListensForSocialBroadcasts;

    public int $postId;
    public string $body = '';
    public ?int $replyingToId = null;
    public string $replyBody = '';

    protected $rules = [
        'body' => 'required|string|min:1|max:1000',
        'replyBody' => 'required|string|min:1|max:1000',
    ];

    public function mount(int $postId): void
    {
        $this->postId = $postId;
    }

    public function getListeners(): array
    {
        return $this->backgroundFeedListener();
    }

    protected function shouldRefreshFromSocialBroadcast(?array $payload): bool
    {
        if (! is_array($payload) || ! isset($payload['postId'])) {
            return true;
        }

        return (int) $payload['postId'] === $this->postId;
    }

    public function startReply(int $commentId): void
    {
        $this->replyingToId = $commentId;
        $this->replyBody = '';
        $this->resetErrorBag('replyBody');
    }

    public function cancelReply(): void
    {
        $this->replyingToId = null;
        $this->replyBody = '';
        $this->resetErrorBag('replyBody');
    }

    public function insertEmoji(string $field, string $emoji, EmojiPicker $picker): void
    {
        if (! in_array($field, ['body', 'replyBody'], true)
            || ! config('social.emoji_picker.enabled', true)) {
            return;
        }

        $this->{$field} = $picker->append($this->{$field}, $emoji);
    }

    public function addComment(PostService $postService, FeedService $feedService): void
    {
        $this->validateOnly('body');

        $this->submitComment($postService, $feedService, $this->body);
        $this->body = '';
    }

    public function addReply(PostService $postService, FeedService $feedService): void
    {
        if (! $this->replyingToId) {
            return;
        }

        $this->validateOnly('replyBody');

        $this->submitComment($postService, $feedService, $this->replyBody, $this->replyingToId);
        $this->cancelReply();
    }

    protected function submitComment(
        PostService $postService,
        FeedService $feedService,
        string $text,
        ?int $parentId = null
    ): void {
        $post = Post::findOrFail($this->postId);

        if (! $feedService->canViewPost($post, Auth::user())) {
            session()->flash('error', 'You cannot comment on this post.');
            return;
        }

        try {
            $postService->addComment($post, Auth::user(), $text, $parentId);
            $this->dispatch('comment-added', postId: $this->postId);
            $this->dispatch('notifications-updated');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    protected function loadComments(Post $post, PostService $postService, CommentTreeBuilder $treeBuilder)
    {
        if ($postService->supportsThreadedReplies()) {
            $flat = $post->comments()
                ->where('status', 'published')
                ->with('user')
                ->get();

            return $treeBuilder->build($flat);
        }

        return $post->comments()
            ->where('status', 'published')
            ->with('user')
            ->latest()
            ->get();
    }

    public function render(FeedService $feedService, PostService $postService, CommentTreeBuilder $treeBuilder)
    {
        $post = Post::query()->findOrFail($this->postId);

        if (! $feedService->canViewPost($post, Auth::user())) {
            return view('livewire.social.post-comments-hidden');
        }

        return view('livewire.social.post-comments', [
            'post' => $post,
            'comments' => $this->loadComments($post, $postService, $treeBuilder),
            'supportsReplies' => $postService->supportsThreadedReplies(),
            'maxNestingDepth' => \App\Models\Comment::maxNestingDepth(),
            'indentCap' => \App\Models\Comment::indentCap(),
        ]);
    }
}
