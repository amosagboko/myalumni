<?php

namespace Tests\Unit\Social;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use App\Services\Social\PostService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesSocialConnections;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use CreatesSocialConnections;
    use DatabaseTransactions;

    private PostService $postService;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->postService = app(PostService::class);
    }

    public function test_connected_user_can_comment_on_post(): void
    {
        $author = User::factory()->create();
        $commenter = User::factory()->create();
        $this->connectUsers($commenter, $author);

        $post = Post::factory()->create(['user_id' => $author->id, 'comments' => 0]);

        $comment = $this->postService->addComment($post, $commenter, 'Great update!');

        $this->assertSame('Great update!', $comment->comment);
        $this->assertSame($post->id, $comment->post_id);
        $this->assertNull($comment->parent_id);
        $this->assertSame(1, $post->fresh()->comments);
    }

    public function test_user_can_reply_to_top_level_comment(): void
    {
        $author = User::factory()->create();
        $replier = User::factory()->create();
        $this->connectUsers($replier, $author);

        $post = Post::factory()->create(['user_id' => $author->id, 'comments' => 0]);
        $parent = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
        ]);

        $reply = $this->postService->addComment($post, $replier, 'Thanks!', $parent->id);

        $this->assertSame($parent->id, $reply->parent_id);
    }

    public function test_user_cannot_reply_to_a_reply(): void
    {
        $author = User::factory()->create();
        $replier = User::factory()->create();
        $this->connectUsers($replier, $author);

        $post = Post::factory()->create(['user_id' => $author->id]);
        $parent = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
        ]);
        $existingReply = Comment::factory()->reply($parent)->create([
            'user_id' => $replier->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You can only reply to top-level comments.');

        $this->postService->addComment($post, $replier, 'Nested reply', $existingReply->id);
    }

    public function test_stranger_cannot_like_post(): void
    {
        $author = User::factory()->create();
        $stranger = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You cannot interact with this post.');

        $this->postService->toggleLike($post, $stranger);
    }

    public function test_create_post_stores_processed_images_with_positions(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $images = [
            UploadedFile::fake()->image('one.jpg', 1200, 900),
            UploadedFile::fake()->image('two.jpg', 800, 800),
        ];

        $post = $this->postService->createPost($user, 'Photo dump', images: $images);

        $media = PostMedia::query()->where('post_id', $post->id)->orderBy('position')->get();

        $this->assertCount(2, $media);
        $this->assertSame(['0', '1'], $media->pluck('position')->all());

        foreach ($media as $item) {
            $this->assertSame('image', $item->getMediaType());
            $this->assertNotNull($item->getMediaPath());
            $this->assertNotNull($item->getThumbPath());
            Storage::disk('public')->assertExists($item->getMediaPath());
            Storage::disk('public')->assertExists($item->getThumbPath());
        }
    }
}
