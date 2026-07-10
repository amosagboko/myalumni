<?php

namespace Tests\Unit\Social;

use App\Models\Post;
use App\Models\User;
use App\Services\Social\FeedService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesSocialConnections;
use Tests\TestCase;

class FeedServiceTest extends TestCase
{
    use CreatesSocialConnections;
    use DatabaseTransactions;

    private FeedService $feedService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->feedService = app(FeedService::class);
    }

    public function test_user_always_sees_own_posts(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->feedService->canViewPost($post, $user));
        $this->assertTrue(
            $this->feedService->feedQuery($user)->where('id', $post->id)->exists()
        );
    }

    public function test_connection_sees_connections_only_post(): void
    {
        $viewer = User::factory()->create();
        $author = User::factory()->create();
        $this->connectUsers($viewer, $author);

        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->assertTrue($this->feedService->canViewPost($post, $viewer));
        $this->assertTrue(
            $this->feedService->feedQuery($viewer)->where('id', $post->id)->exists()
        );
    }

    public function test_stranger_cannot_see_connections_only_post(): void
    {
        $viewer = User::factory()->create();
        $author = User::factory()->create();

        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->assertFalse($this->feedService->canViewPost($post, $viewer));
        $this->assertFalse(
            $this->feedService->feedQuery($viewer)->where('id', $post->id)->exists()
        );
    }

    public function test_alumni_role_user_sees_all_alumni_post(): void
    {
        Role::firstOrCreate(['name' => 'alumni', 'guard_name' => 'web']);

        $viewer = User::factory()->create();
        $viewer->assignRole('alumni');

        $author = User::factory()->create();
        $post = Post::factory()->allAlumni()->create(['user_id' => $author->id]);

        $this->assertTrue($this->feedService->canViewPost($post, $viewer));
        $this->assertTrue(
            $this->feedService->feedQuery($viewer)->where('id', $post->id)->exists()
        );
    }

    public function test_feed_pagination_uses_configured_page_size(): void
    {
        config(['social.feed_per_page' => 5]);

        $user = User::factory()->create();
        Post::factory()->count(6)->create(['user_id' => $user->id]);

        $paginator = $this->feedService->paginateFeed($user);

        $this->assertSame(5, $paginator->perPage());
        $this->assertSame(6, $paginator->total());
    }
}
