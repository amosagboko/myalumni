<?php

namespace App\Livewire;

use DateTime;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Livewire\Component;
use App\Models\PostMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Returnpost extends Component
{
    public $newComment = '';

    public function like($postId) {
        $userId = Auth::id();
    
        DB::beginTransaction();
        try {
            // Get the post
            $post = Post::findOrFail($postId);

            // Check if user can like this post
            if (!$post->canBeViewedBy(Auth::user())) {
                toastr()->error('You cannot like this post.');
                return;
            }

            // Attempt to find an existing like record
            $like = Like::where(['post_id' => $postId, 'user_id' => $userId])->first();
    
            if ($like) {
                // If the like exists, delete it and decrement the likes count
                $like->delete();
                $post->decrement('likes');
            } else {
                // If the like does not exist, create it and increment the likes count
                Like::create(['post_id' => $postId, 'user_id' => $userId]);
                $post->increment('likes');
            }
    
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function addComment($postId)
    {
        $this->validate([
            'newComment' => 'required|min:1|max:1000'
        ]);

        $post = Post::findOrFail($postId);

        // Check if user can comment on this post
        if (!$post->canBeViewedBy(Auth::user())) {
            toastr()->error('You cannot comment on this post.');
            return;
        }

        $post->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $this->newComment
        ]);

        $this->newComment = '';
        toastr()->success('Comment added successfully.');
    }

    public function render()
    {
        $user = Auth::user();

        // Get posts visible to the current user with all necessary relationships
        $posts = Post::visibleTo($user)
            ->with([
                'media',
                'user',
                'likes',
                'comments.user'
            ])
            ->latest()
            ->get();
         
        return view('livewire.returnpost', [
            'posts' => $posts
        ]);
    }
}
