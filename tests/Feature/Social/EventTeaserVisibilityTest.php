<?php

namespace Tests\Feature\Social;

use App\Livewire\Social\FeedAnnouncementsStrip;
use App\Livewire\Social\FeedOfficialEventsTeaser;
use App\Models\Event;
use App\Models\User;
use App\Services\Social\EventService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventTeaserVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');

        config([
            'social.events_sidebar_teaser_limit' => 3,
            'social.events_announcements_strip_limit' => 3,
        ]);
    }

    public function test_fourth_upcoming_event_appears_in_sidebar_teaser(): void
    {
        $admin = User::factory()->create();
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');

        foreach ([
            'Alpha Event',
            'Bravo Event',
            'Charlie Event',
            'Delta Event',
        ] as $index => $name) {
            Event::create([
                'user_id' => $admin->id,
                'type' => 'opportunity',
                'eventname' => $name,
                'date' => now()->addWeeks($index + 1)->toDateString(),
                'venue' => 'Campus',
                'is_published' => true,
            ]);
        }

        Livewire::actingAs($alumni)
            ->test(FeedOfficialEventsTeaser::class)
            ->assertSee('Delta Event');
    }

    public function test_soonest_upcoming_events_are_prioritized_in_teaser(): void
    {
        $admin = User::factory()->create();

        Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => 'Far Future Event',
            'date' => now()->addYear()->toDateString(),
            'venue' => 'Campus',
            'is_published' => true,
        ]);

        Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => 'Soonest Event',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Campus',
            'is_published' => true,
        ]);

        $teaser = app(EventService::class)->teaser(10)->whereIn('eventname', [
            'Far Future Event',
            'Soonest Event',
        ]);

        $this->assertSame('Soonest Event', $teaser->first()?->eventname);
    }

    public function test_past_published_event_is_excluded_from_upcoming_teaser(): void
    {
        $admin = User::factory()->create();

        Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => 'Past Published Event',
            'date' => now()->subWeek()->toDateString(),
            'venue' => 'Campus',
            'is_published' => true,
        ]);

        $names = app(EventService::class)->teaser(10)->pluck('eventname');

        $this->assertFalse($names->contains('Past Published Event'));
    }

    public function test_announcements_strip_limits_to_three_most_recently_created(): void
    {
        $admin = User::factory()->create();
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');

        foreach (range(1, 4) as $index) {
            $event = Event::create([
                'user_id' => $admin->id,
                'type' => 'opportunity',
                'eventname' => "Strip Event {$index}",
                'date' => now()->addWeeks($index)->toDateString(),
                'venue' => 'Campus',
                'is_published' => true,
            ]);

            $event->forceFill([
                'created_at' => now()->subMinutes(10 - $index),
                'updated_at' => now()->subMinutes(10 - $index),
            ])->save();
        }

        $names = app(EventService::class)->stripCarouselByType('opportunity', 3)->pluck('eventname');

        $this->assertCount(3, $names);
        $this->assertSame([
            'Strip Event 2',
            'Strip Event 3',
            'Strip Event 4',
        ], $names->all());

        Livewire::actingAs($alumni)
            ->test(FeedAnnouncementsStrip::class)
            ->assertSee('Strip Event 4')
            ->assertSee('Strip Event 3')
            ->assertSee('Strip Event 2')
            ->assertDontSee('Strip Event 1');
    }

    public function test_announcements_strip_separates_highlights_news_and_events(): void
    {
        $admin = User::factory()->create();
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');

        Event::create([
            'user_id' => $admin->id,
            'type' => 'connect',
            'eventname' => 'Featured Alumni Story',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Campus',
            'is_published' => true,
        ]);

        Event::create([
            'user_id' => $admin->id,
            'type' => 'event',
            'eventname' => 'Campus News Update',
            'date' => now()->addWeeks(2)->toDateString(),
            'venue' => 'Campus',
            'is_published' => true,
        ]);

        Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => 'Official Alumni Gala',
            'date' => now()->addWeeks(3)->toDateString(),
            'venue' => 'Campus',
            'is_published' => true,
        ]);

        Livewire::actingAs($alumni)
            ->test(FeedAnnouncementsStrip::class)
            ->assertSee('Highlights')
            ->assertSee('News')
            ->assertSee('Events')
            ->assertSee('feedAnnouncementsHighlightsCarousel', false)
            ->assertSee('feedAnnouncementsNewsCarousel', false)
            ->assertSee('feedAnnouncementsEventsCarousel', false)
            ->assertSee('Featured Alumni Story')
            ->assertSee('Campus News Update')
            ->assertSee('Official Alumni Gala');
    }

    public function test_sidebar_teaser_limits_to_three_most_recent_events(): void
    {
        $admin = User::factory()->create();
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');

        $prefix = 'SidebarStrip'.uniqid();

        foreach (range(1, 5) as $index) {
            $event = Event::create([
                'user_id' => $admin->id,
                'type' => 'opportunity',
                'eventname' => "{$prefix} {$index}",
                'date' => now()->addYear()->toDateString(),
                'venue' => 'Campus',
                'is_published' => true,
            ]);

            $event->forceFill([
                'created_at' => now()->subMinutes(10 - $index),
                'updated_at' => now()->subMinutes(10 - $index),
            ])->save();
        }

        $items = app(EventService::class)->stripCarouselByType('opportunity', 3);

        $this->assertCount(3, $items);
        $this->assertFalse($items->pluck('eventname')->contains("{$prefix} 1"));
        $this->assertTrue($items->pluck('eventname')->contains("{$prefix} 5"));

        Livewire::actingAs($alumni)
            ->test(FeedOfficialEventsTeaser::class)
            ->assertSee("{$prefix} 5")
            ->assertDontSee("{$prefix} 1");
    }

    public function test_sidebar_teaser_renders_event_carousel(): void
    {
        $admin = User::factory()->create();
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');

        Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => 'Carousel Sidebar Event',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Campus',
            'is_published' => true,
        ]);

        Livewire::actingAs($alumni)
            ->test(FeedOfficialEventsTeaser::class)
            ->assertSee('feed-events-carousel', false)
            ->assertSee('feedSidebarEventsCarousel', false)
            ->assertSee('Carousel Sidebar Event');
    }
}
