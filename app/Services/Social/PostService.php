<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\Event;
use App\Models\Comment;
use App\Models\Like;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class PostService
{
    public function __construct(
        protected FeedService $feedService
    ) {}

    public function createPost(
        User $user,
        string $content,
        string $visibility = FeedService::VISIBILITY_CONNECTIONS,
        array $images = [],
        array $videos = [],
        ?int $eventId = null
    ): Post {
        return DB::transaction(function () use ($user, $content, $visibility, $images, $videos, $eventId) {
            if ($eventId) {
                Event::published()->findOrFail($eventId);
            }

            $post = Post::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'content' => $content,
                'status' => 'published',
                'visibility' => $visibility,
                'event_id' => $eventId,
                'likes' => 0,
                'comments' => 0,
            ]);

            foreach ($images as $image) {
                $this->storeMedia($post, $image, 'image');
            }

            foreach ($videos as $video) {
                $this->storeMedia($post, $video, 'video');
            }

            return $post->load(['user', 'media', 'event']);
        });
    }

    protected function storeMedia(Post $post, UploadedFile $file, string $type): void
    {
        $folder = $type === 'video' ? 'post-videos' : 'post-images';
        $path = $file->store($folder, 'public');

        PostMedia::create([
            'post_id' => $post->id,
            'filetype' => $type,
            'file' => [
                'media_path' => $path,
                'media_type' => $type,
            ],
        ]);
    }

    public function toggleLike(Post $post, User $user): void
    {
        if (!$this->feedService->canViewPost($post, $user)) {
            throw new \RuntimeException('You cannot interact with this post.');
        }

        DB::transaction(function () use ($post, $user) {
            $like = Like::where('post_id', $post->id)->where('user_id', $user->id)->first();

            if ($like) {
                $like->delete();
                $post->decrement('likes');
            } else {
                Like::create(['post_id' => $post->id, 'user_id' => $user->id]);
                $post->increment('likes');
            }
        });
    }

    public function addComment(Post $post, User $user, string $comment): Comment
    {
        if (!$this->feedService->canViewPost($post, $user)) {
            throw new \RuntimeException('You cannot comment on this post.');
        }

        $commentModel = $post->comments()->create([
            'user_id' => $user->id,
            'comment' => $comment,
            'status' => 'published',
        ]);

        $post->increment('comments');

        return $commentModel->load('user');
    }

    public function userHasLiked(Post $post, User $user): bool
    {
        return $post->likes()->where('user_id', $user->id)->exists();
    }
}
