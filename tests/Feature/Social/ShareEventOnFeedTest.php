<?php

namespace Tests\Feature\Social;

use App\Livewire\Social\Feed;
use App\Livewire\Social\PostComposer;
use App\Models\Event;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShareEventOnFeedTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    private function publishedUpcomingEvent(User $owner): Event
    {
        return Event::create([
            'user_id' => $owner->id,
            'type' => 'opportunity',
            'eventname' => 'Alumni Homecoming Gala',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Main Auditorium',
            'is_published' => true,
        ]);
    }

    public function test_event_show_share_button_links_to_home_with_event_param(): void
    {
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');
        $admin = User::factory()->create();

        $event = $this->publishedUpcomingEvent($admin);

        $response = $this->actingAs($alumni)->get(route('alumni.events.show', $event));

        $response->assertOk();
        $response->assertSee(route('alumni.home', ['share_event' => $event->id]), false);
    }

    public function test_composer_preselects_event_from_share_param(): void
    {
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');
        $admin = User::factory()->create();

        $event = $this->publishedUpcomingEvent($admin);

        Livewire::actingAs($alumni)
            ->withQueryParams(['share_event' => $event->id])
            ->test(PostComposer::class)
            ->assertSet('sharedEventId', $event->id)
            ->assertSet('focusOnShare', true)
            ->assertSee('Event ready to share');
    }

    public function test_composer_ignores_invalid_share_param(): void
    {
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');

        Livewire::actingAs($alumni)
            ->withQueryParams(['share_event' => 999999])
            ->test(PostComposer::class)
            ->assertSet('sharedEventId', null)
            ->assertSet('focusOnShare', false);
    }

    public function test_shared_event_creates_post_with_event_reference(): void
    {
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');
        $admin = User::factory()->create();

        $event = $this->publishedUpcomingEvent($admin);

        Livewire::actingAs($alumni)
            ->withQueryParams(['share_event' => $event->id])
            ->test(PostComposer::class)
            ->call('createPost')
            ->assertHasNoErrors()
            ->assertSet('sharedEventId', null)
            ->assertSet('focusOnShare', false);

        $this->assertDatabaseHas('posts', [
            'user_id' => $alumni->id,
            'event_id' => $event->id,
        ]);
    }

    public function test_shared_event_post_renders_thumbnail_with_lightbox(): void
    {
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');
        $admin = User::factory()->create();

        $event = Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => 'Thumbnail Share Gala',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Main Auditorium',
            'image' => 'events/test-share.jpg',
            'is_published' => true,
        ]);

        $post = Post::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $alumni->id,
            'content' => 'Shared an official event.',
            'status' => 'published',
            'visibility' => 'all_alumni',
            'event_id' => $event->id,
            'likes' => 0,
        ]);

        Livewire::actingAs($alumni)
            ->test(Feed::class)
            ->assertSee('Thumbnail Share Gala')
            ->assertSee('post-media-thumb', false)
            ->assertSee('data-lightbox="post-'.$post->id.'"', false)
            ->assertDontSee('img-fluid rounded-xxl mt-3 w-100', false);
    }
}
