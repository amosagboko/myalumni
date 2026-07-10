<?php

namespace Tests\Feature\Social;

use App\Livewire\Social\Feed;
use App\Livewire\Social\PostComments;
use App\Livewire\Social\PostComposer;
use App\Models\Post;
use App\Models\User;
use App\Services\Social\FeedService;
use App\Services\Social\PostService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesSocialConnections;
use Tests\TestCase;

class AlumniSocialInteractionsTest extends TestCase
{
    use CreatesSocialConnections;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    public function test_alumni_can_create_post_without_page_reload_flow(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PostComposer::class)
            ->set('content', 'Hello alumni network')
            ->call('createPost')
            ->assertHasNoErrors()
            ->assertDispatched('post-created');

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'content' => 'Hello alumni network',
        ]);
    }

    public function test_connected_alumni_can_like_post(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();
        $this->connectUsers($viewer, $author);

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'likes' => 0,
        ]);

        Livewire::actingAs($viewer)
            ->test(Feed::class)
            ->call('toggleLike', $post->id)
            ->assertHasNoErrors()
            ->assertSee('1 Like');

        $this->assertDatabaseHas('likes', [
            'post_id' => $post->id,
            'user_id' => $viewer->id,
        ]);
        $this->assertSame(1, $post->fresh()->likes);
    }

    public function test_connected_alumni_can_comment_on_post(): void
    {
        $author = User::factory()->create();
        $commenter = User::factory()->create();
        $this->connectUsers($commenter, $author);

        $post = Post::factory()->create([
            'user_id' => $author->id,
            'comments' => 0,
        ]);

        Livewire::actingAs($commenter)
            ->test(PostComments::class, ['postId' => $post->id])
            ->set('body', 'Nice post!')
            ->call('addComment')
            ->assertHasNoErrors()
            ->assertDispatched('comment-added', postId: $post->id);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $commenter->id,
            'comment' => 'Nice post!',
        ]);
    }

    public function test_all_alumni_video_post_is_visible_to_another_alumni(): void
    {
        Storage::fake('public');

        $author = User::factory()->create();
        $viewer = User::factory()->create();
        $author->assignRole('alumni');
        $viewer->assignRole('alumni');

        $video = UploadedFile::fake()->create('update.mp4', 1024, 'video/mp4');

        $post = app(PostService::class)->createPost(
            $author,
            'Video for all alumni',
            FeedService::VISIBILITY_ALL_ALUMNI,
            videos: [$video],
        );

        $media = $post->media->first();

        Livewire::actingAs($viewer)
            ->test(Feed::class)
            ->assertSee('Video for all alumni')
            ->assertSee('/storage/'.$media->getMediaPath(), false);
    }
}
