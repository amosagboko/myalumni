<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Comment;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PostComments extends Component
{
    public $postId;
    public $comment;
    public $comments;

    protected $rules = [
        'comment' => 'required|string|max:255',
    ];

    public function mount($postId)
    {
        $this->postId = $postId;
        $this->loadComments();
    }

    public function loadComments()
    {
        $this->comments = Comment::with(['user', 'post.user'])
            ->where('post_id', $this->postId)
            ->where('status', 'published')
            ->latest()
            ->get();
    }

    public function addComment()
    {
        $this->validate();

        // Get the post
        $post = \App\Models\Post::findOrFail($this->postId);

        // Check if user can comment on this post
        if (!$post->canBeViewedBy(Auth::user())) {
            toastr()->error('You cannot comment on this post.');
            return;
        }

        try {
            Comment::create([
                'post_id' => $this->postId,
                'user_id' => Auth::id(),
                'comment' => $this->comment,
                'status' => 'published',
            ]);

            // Increment comment count
            $post->increment('comments');

            $this->comment = '';
            $this->loadComments();
            toastr()->success('Comment added successfully!');

        } catch (\Exception $e) {
            toastr()->error('Error adding comment: ' . $e->getMessage());
        }
    }

    public function deleteComment($commentId)
    {
        $comment = Comment::findOrFail($commentId);

        // Check if user owns the comment or is the post owner
        if ($comment->user_id !== Auth::id() && $comment->post->user_id !== Auth::id()) {
            toastr()->error('You are not authorized to delete this comment.');
            return;
        }

        try {
            $comment->delete();
            $comment->post->decrement('comments');
            $this->loadComments();
            toastr()->success('Comment deleted successfully!');
        } catch (\Exception $e) {
            toastr()->error('Error deleting comment: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.post-comments');
    }
}
