<?php

namespace App\Livewire;

use App\Services\Social\FeedService;
use App\Services\Social\PostService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * @deprecated Use App\Livewire\Social\Feed on alumni social pages.
 */
class Returnpost extends Component
{
    public $newComment = '';

    protected $listeners = ['post-created' => '$refresh'];

    public function like($postId, PostService $postService, FeedService $feedService): void
    {
        $post = \App\Models\Post::findOrFail($postId);

        if (!$feedService->canViewPost($post, Auth::user())) {
            session()->flash('error', 'You cannot like this post.');
            return;
        }

        try {
            $postService->toggleLike($post, Auth::user());
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function addComment($postId, PostService $postService, FeedService $feedService): void
    {
        $this->validate([
            'newComment' => 'required|min:1|max:1000',
        ]);

        $post = \App\Models\Post::findOrFail($postId);

        if (!$feedService->canViewPost($post, Auth::user())) {
            session()->flash('error', 'You cannot comment on this post.');
            return;
        }

        try {
            $postService->addComment($post, Auth::user(), $this->newComment);
            $this->newComment = '';
            session()->flash('success', 'Comment added successfully.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(FeedService $feedService)
    {
        return view('livewire.returnpost', [
            'posts' => $feedService->feedQuery(Auth::user())->get(),
        ]);
    }
}
