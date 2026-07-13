<?php

namespace Tests\Feature\Social;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventShowPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    public function test_event_show_page_renders_contained_layout(): void
    {
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');

        $admin = User::factory()->create();

        $event = Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => 'Professional Gala Night',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Senate Hall',
            'description' => 'An evening with alumni leaders.',
            'image' => 'events/test-event.jpg',
            'is_published' => true,
        ]);

        $response = $this->actingAs($alumni)->get(route('alumni.events.show', $event));

        $response->assertOk();
        $response->assertSee('Professional Gala Night');
        $response->assertSee('About this event');
        $response->assertSee('event-show-page__thumbnail', false);
        $response->assertSee('data-lightbox="event-'.$event->id.'"', false);
        $response->assertSee('Tap to view full image');
        $response->assertSee('Back to Discover');
    }
}
