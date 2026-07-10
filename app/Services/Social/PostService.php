<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\Event;
use App\Models\Comment;
use App\Models\Like;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class PostService
{
    public function __construct(
        protected FeedService $feedService,
        protected NotificationService $notificationService,
        protected SocialBroadcastService $broadcastService
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

            $attributes = [
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'content' => $content,
                'status' => 'published',
                'likes' => 0,
                'comments' => 0,
            ];

            if ($this->feedService->supportsVisibility()) {
                $attributes['visibility'] = $visibility;
            }

            if ($eventId && Schema::hasColumn('posts', 'event_id')) {
                $attributes['event_id'] = $eventId;
            }

            $post = Post::create($attributes);

            foreach ($images as $image) {
                $this->storeMedia($post, $image, 'image');
            }

            foreach ($videos as $video) {
                $this->storeMedia($post, $video, 'video');
            }

            $relations = ['user', 'media'];
            if (Schema::hasColumn('posts', 'event_id')) {
                $relations[] = 'event';
            }

            $post = $post->load($relations);
            $this->broadcastService->feedUpdated('post.created', $post->id);

            return $post;
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

                $post->loadMissing('user');
                if ($post->user) {
                    $this->notificationService->postLiked($post->user, $user, $post);
                }
            }
        });

        $this->broadcastService->feedUpdated('post.liked', $post->id);
    }

    public function addComment(Post $post, User $user, string $comment, ?int $parentId = null): Comment
    {
        if (!$this->feedService->canViewPost($post, $user)) {
            throw new \RuntimeException('You cannot comment on this post.');
        }

        $parent = null;
        if ($parentId) {
            if (! Schema::hasColumn('comments', 'parent_id')) {
                throw new \RuntimeException('Replies are not available yet. Please run database migrations.');
            }

            $parent = Comment::query()
                ->where('post_id', $post->id)
                ->findOrFail($parentId);
        }

        $attributes = [
            'user_id' => $user->id,
            'comment' => $comment,
            'status' => 'published',
        ];

        if ($parentId && Schema::hasColumn('comments', 'parent_id')) {
            $attributes['parent_id'] = $parentId;
        }

        $commentModel = $post->comments()->create($attributes);

        $post->increment('comments');

        if ($parent) {
            $parent->loadMissing('user');
            if ($parent->user && $parent->user->id !== $user->id) {
                $this->notificationService->commentReplied($parent->user, $user, $post);
            }
        } else {
            $post->loadMissing('user');
            if ($post->user && $post->user->id !== $user->id) {
                $this->notificationService->postCommented($post->user, $user, $post);
            }
        }

        $this->broadcastService->feedUpdated('comment.added', $post->id);

        return $commentModel->load('user');
    }

    public function supportsThreadedReplies(): bool
    {
        return Schema::hasColumn('comments', 'parent_id');
    }

    public function userHasLiked(Post $post, User $user): bool
    {
        return $post->likes()->where('user_id', $user->id)->exists();
    }
}
