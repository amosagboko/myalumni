<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_landing_page_renders_content_thumbnails_with_lightbox(): void
    {
        $admin = User::factory()->create();

        Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => 'Public Landing Gala',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Main Auditorium',
            'description' => 'A public landing page event teaser.',
            'image' => 'events/landing-test.jpg',
            'is_published' => true,
            'order' => 0,
        ]);

        Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => 'Second Landing Event',
            'date' => now()->addWeeks(2)->toDateString(),
            'venue' => 'Senate Hall',
            'description' => 'Another landing page event.',
            'image' => 'events/landing-test-2.jpg',
            'is_published' => true,
            'order' => 1,
        ]);

        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee('Highlights');
        $response->assertSee('Public Landing Gala');
        $response->assertSee('landing-content-carousel', false);
        $response->assertSee('landingEventsCarousel', false);
        $response->assertSee('carousel-indicators', false);
        $response->assertSee('landing-content-item__thumb', false);
        $response->assertSee('data-lightbox="landing-events"', false);
        $response->assertSee('/js/lightbox.js', false);
        $response->assertDontSee('max-height: 150px', false);
    }
}
