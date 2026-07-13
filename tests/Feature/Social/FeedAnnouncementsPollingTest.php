<?php

namespace Tests\Feature\Social;

use App\Livewire\Social\FeedAnnouncementsStrip;
use App\Livewire\Social\FeedOfficialEventsTeaser;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FeedAnnouncementsPollingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');

        config([
            'social.realtime_enabled' => false,
            'social.poll_interval_seconds' => 30,
        ]);
    }

    public function test_announcements_strip_polls_and_refreshes_upcoming_events(): void
    {
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');

        $admin = User::factory()->create();
        $eventName = 'Poll Event '.Str::random(6);

        Livewire::actingAs($alumni)
            ->test(FeedAnnouncementsStrip::class)
            ->assertSee('wire:poll.visible.30s', false)
            ->assertDontSee($eventName);

        Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => $eventName,
            'date' => now()->addDays(7)->toDateString(),
            'venue' => 'Main Campus',
            'is_published' => true,
        ]);

        Livewire::actingAs($alumni)
            ->test(FeedAnnouncementsStrip::class)
            ->call('refreshQuietly')
            ->assertSee($eventName);
    }

    public function test_sidebar_events_teaser_polls_and_refreshes_upcoming_events(): void
    {
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');

        $admin = User::factory()->create();
        $eventName = 'Sidebar Event '.Str::random(6);

        Livewire::actingAs($alumni)
            ->test(FeedOfficialEventsTeaser::class)
            ->assertSee('wire:poll.visible.30s', false)
            ->assertDontSee($eventName);

        Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => $eventName,
            'date' => now()->addDays(3)->toDateString(),
            'venue' => 'Senate Hall',
            'is_published' => true,
        ]);

        Livewire::actingAs($alumni)
            ->test(FeedOfficialEventsTeaser::class)
            ->call('refreshQuietly')
            ->assertSee($eventName);
    }
}
