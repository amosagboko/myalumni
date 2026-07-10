<?php

namespace Tests\Feature\Social;

use App\Livewire\Social\Feed;
use App\Livewire\Social\PostComments;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class FeedRefreshTest extends TestCase
{
    use DatabaseTransactions;

    public function test_feed_keeps_existing_posts_after_post_created_event(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Feed::class)
            ->assertSee($posts[0]->content)
            ->assertSee($posts[1]->content)
            ->assertSee($posts[2]->content)
            ->dispatch('post-created')
            ->assertSee($posts[0]->content)
            ->assertSee($posts[1]->content)
            ->assertSee($posts[2]->content);
    }

    public function test_feed_keeps_posts_after_comment_added_event(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(2)->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Feed::class)
            ->assertSee($posts[0]->content)
            ->assertSee($posts[1]->content)
            ->dispatch('comment-added', postId: $posts[0]->id)
            ->assertSee($posts[0]->content)
            ->assertSee($posts[1]->content);
    }

    public function test_feed_like_updates_count_without_losing_other_posts(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(2)->create(['user_id' => $user->id, 'likes' => 0]);

        Livewire::actingAs($user)
            ->test(Feed::class)
            ->assertSee($posts[0]->content)
            ->assertSee($posts[1]->content)
            ->call('toggleLike', $posts[0]->id)
            ->assertSee($posts[0]->content)
            ->assertSee($posts[1]->content)
            ->assertSee('1 Like');
    }

    public function test_post_comments_updates_feed_comment_count(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id, 'comments' => 0]);

        Livewire::actingAs($user)
            ->test(Feed::class)
            ->call('toggleComments', $post->id);

        Livewire::actingAs($user)
            ->test(PostComments::class, ['postId' => $post->id])
            ->set('body', 'Great update')
            ->call('addComment')
            ->assertHasNoErrors();

        Livewire::actingAs($user)
            ->test(Feed::class)
            ->assertSee('1 Comments');
    }
}
