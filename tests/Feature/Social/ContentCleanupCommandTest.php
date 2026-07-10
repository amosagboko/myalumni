<?php

namespace Tests\Feature\Social;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ContentCleanupCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cleanup_skips_when_retention_disabled(): void
    {
        config(['social.content_retention_days' => 0]);

        $post = Post::factory()->create([
            'created_at' => now()->subDays(60),
        ]);

        Artisan::call('content:cleanup');

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
        $this->assertStringContainsString(
            'retention is disabled',
            Artisan::output()
        );
    }

    public function test_dry_run_does_not_delete_posts(): void
    {
        config(['social.content_retention_days' => 30]);

        $post = Post::factory()->create([
            'created_at' => now()->subDays(45),
        ]);

        Artisan::call('content:cleanup', ['--dry-run' => true]);

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
        $this->assertStringContainsString('Dry run', Artisan::output());
    }

    public function test_cleanup_deletes_old_published_posts_and_cascades_comments(): void
    {
        config(['social.content_retention_days' => 30]);

        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(45),
        ]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'created_at' => now()->subDays(45),
        ]);

        Artisan::call('content:cleanup');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_cleanup_leaves_recent_comments_on_surviving_posts(): void
    {
        config(['social.content_retention_days' => 30]);

        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(5),
        ]);
        $oldComment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'created_at' => now()->subDays(45),
        ]);

        Artisan::call('content:cleanup');

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
        $this->assertDatabaseHas('comments', ['id' => $oldComment->id]);
    }
}
