<?php

namespace Tests\Feature\Social;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DiscoverPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    public function test_discover_page_renders_for_alumni(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $response = $this->actingAs($user)->get(route('alumni.discover'));

        $response->assertOk();
        $response->assertSee('Discover');
        $response->assertSee('Highlights');
        $response->assertSee('News');
        $response->assertSee('Events');
        $response->assertSee('Upcoming');
        $response->assertSee('Upcoming Events');
        $response->assertSee('discover-page', false);
        $response->assertSee('wire:poll.visible.30s', false);
        $response->assertSee('wire:snapshot', false);
    }

    public function test_legacy_events_url_redirects_to_discover(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $response = $this->actingAs($user)->get('/alumni/events');

        $response->assertRedirect('/alumni/discover');
    }

    public function test_discover_page_filters_by_tab_type(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $admin = User::factory()->create();

        Event::create([
            'user_id' => $admin->id,
            'type' => 'connect',
            'eventname' => 'Featured Alumni Spotlight',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Campus',
            'is_published' => true,
        ]);

        Event::create([
            'user_id' => $admin->id,
            'type' => 'opportunity',
            'eventname' => 'Campus Reunion Night',
            'date' => now()->addWeeks(2)->toDateString(),
            'venue' => 'Senate Hall',
            'is_published' => true,
        ]);

        $eventService = app(\App\Services\Social\EventService::class);

        $highlightNames = $eventService->paginateByType('connect', 'upcoming', 9)->pluck('eventname');
        $eventNames = $eventService->paginateByType('opportunity', 'upcoming', 9)->pluck('eventname');

        $this->assertTrue($highlightNames->contains('Featured Alumni Spotlight'));
        $this->assertFalse($highlightNames->contains('Campus Reunion Night'));
        $this->assertTrue($eventNames->contains('Campus Reunion Night'));
        $this->assertFalse($eventNames->contains('Featured Alumni Spotlight'));

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Social\Discover::class)
            ->call('setTab', 'news')
            ->assertSet('tab', 'news');
    }

    public function test_discover_page_paginates_six_items_per_tab(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $admin = User::factory()->create();
        $prefix = 'DiscoverPaginate'.uniqid();

        foreach (range(1, 7) as $index) {
            Event::create([
                'user_id' => $admin->id,
                'type' => 'event',
                'eventname' => "{$prefix} {$index}",
                'date' => now()->addYear()->toDateString(),
                'venue' => 'Campus',
                'order' => $index,
                'is_published' => true,
            ]);
        }

        $paginator = app(\App\Services\Social\EventService::class)->paginateByType('event', 'upcoming', 6);

        $this->assertSame(6, $paginator->perPage());
        $this->assertCount(6, $paginator->items());

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Social\Discover::class, ['tab' => 'news'])
            ->assertSee("{$prefix} 1")
            ->assertSee("{$prefix} 6")
            ->assertDontSee("{$prefix} 7");
    }
}
