<?php

namespace App\Livewire\Social;

use App\Models\Post;
use App\Services\Social\FeedService;
use App\Services\Social\PostService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PostComments extends Component
{
    public int $postId;
    public string $body = '';

    protected $rules = [
        'body' => 'required|string|min:1|max:1000',
    ];

    public function mount(int $postId): void
    {
        $this->postId = $postId;
    }

    public function addComment(PostService $postService, FeedService $feedService): void
    {
        $this->validate();

        $post = Post::findOrFail($this->postId);

        if (!$feedService->canViewPost($post, Auth::user())) {
            session()->flash('error', 'You cannot comment on this post.');
            return;
        }

        try {
            $postService->addComment($post, Auth::user(), $this->body);
            $this->body = '';
            $this->dispatch('comment-added');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(FeedService $feedService)
    {
        $post = Post::with(['comments.user'])->findOrFail($this->postId);

        if (!$feedService->canViewPost($post, Auth::user())) {
            return view('livewire.social.post-comments-hidden');
        }

        return view('livewire.social.post-comments', [
            'post' => $post,
            'comments' => $post->comments()->with('user')->latest()->get(),
        ]);
    }
}
