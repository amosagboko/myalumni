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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class PostService
{
    public function __construct(
        protected FeedService $feedService,
        protected NotificationService $notificationService,
        protected SocialBroadcastService $broadcastService,
        protected PostImageProcessor $imageProcessor,
    ) {}

    public function createPost(
        User $user,
        string $content,
        string $visibility = FeedService::VISIBILITY_CONNECTIONS,
        array $images = [],
        array $videos = [],
        ?int $eventId = null
    ): Post {
        $post = DB::transaction(function () use ($user, $content, $visibility, $images, $videos, $eventId) {
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

            foreach ($images as $position => $image) {
                $this->storeMedia($post, $image, 'image', $position);
            }

            foreach ($videos as $position => $video) {
                $this->storeMedia($post, $video, 'video', count($images) + $position);
            }

            $relations = ['user', 'media'];
            if (Schema::hasColumn('posts', 'event_id')) {
                $relations[] = 'event';
            }

            return $post->load($relations);
        });

        $this->broadcastService->feedUpdated('post.created', $post->id, $user->id);

        return $post;
    }

    protected function storeMedia(Post $post, UploadedFile $file, string $type, int $position = 0): void
    {
        if ($type === 'image') {
            $payload = $this->imageProcessor->process($file);

            PostMedia::create([
                'post_id' => $post->id,
                'filetype' => $type,
                'position' => $position,
                'file' => $payload,
            ]);

            return;
        }

        $path = $file->store('post-videos', 'public');

        PostMedia::create([
            'post_id' => $post->id,
            'filetype' => $type,
            'position' => $position,
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

        $liked = false;

        DB::transaction(function () use ($post, $user, &$liked) {
            $like = Like::where('post_id', $post->id)->where('user_id', $user->id)->first();

            if ($like) {
                $like->delete();
                $post->decrement('likes');
            } else {
                Like::create(['post_id' => $post->id, 'user_id' => $user->id]);
                $post->increment('likes');
                $liked = true;
            }
        });

        if ($liked) {
            $post->loadMissing('user');
            if ($post->user && $post->user->id !== $user->id) {
                $this->notificationService->postLiked($post->user, $user, $post);
            }
        }

        $this->broadcastService->feedUpdated('post.liked', $post->id, $user->id);
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

            if (! $parent->canAcceptReply()) {
                throw new \RuntimeException('Maximum reply depth reached.');
            }
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

        $this->broadcastService->feedUpdated('comment.added', $post->id, $user->id);

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

    public function canDeletePost(Post $post, User $user): bool
    {
        return (int) $post->user_id === (int) $user->id;
    }

    public function deletePost(Post $post, User $user): void
    {
        if (! $this->canDeletePost($post, $user)) {
            throw new \RuntimeException('You can only delete your own posts.');
        }

        $postId = $post->id;

        DB::transaction(function () use ($post) {
            $post->loadMissing('media');

            foreach ($post->media as $media) {
                $this->deleteStoredMediaFiles($media);
            }

            $post->delete();
        });

        $this->broadcastService->feedUpdated('post.deleted', $postId, $user->id);
    }

    protected function deleteStoredMediaFiles(PostMedia $media): void
    {
        $payload = is_array($media->file) ? $media->file : [];

        foreach (['media_path', 'thumb_path'] as $key) {
            $path = $payload[$key] ?? null;
            if (is_string($path) && $path !== '') {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
