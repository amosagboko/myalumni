<?php

namespace Tests\Feature\Alumni;

use App\Livewire\Alumni\CreateEvent;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlumniCreateEventTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
        Role::findOrCreate('alumni-president');

        foreach (['create event', 'view events'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_alumni_with_permission_can_view_create_event_page(): void
    {
        $user = $this->alumniUser(withCreatePermission: true);

        $response = $this->actingAs($user)->get(route('alumni.events.create'));

        $response->assertOk();
        $response->assertSee('Create event');
        $response->assertSee('Submit event');
    }

    public function test_alumni_without_permission_cannot_view_create_event_page(): void
    {
        $user = $this->alumniUser(withCreatePermission: false);

        $response = $this->actingAs($user)->get(route('alumni.events.create'));

        $response->assertForbidden();
    }

    public function test_alumni_can_submit_community_event_for_review(): void
    {
        $user = $this->alumniUser(withCreatePermission: true);

        Livewire::actingAs($user)
            ->test(CreateEvent::class)
            ->set('eventname', 'Class reunion dinner')
            ->set('date', now()->addMonth()->toDateString())
            ->set('venue', 'Main hall')
            ->set('description', 'An evening for the class of 2020.')
            ->call('save')
            ->assertRedirect(route('alumni.events.mine'));

        $event = Event::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($event);
        $this->assertSame('Class reunion dinner', $event->eventname);
        $this->assertSame('opportunity', $event->type);
        $this->assertFalse($event->is_published);
    }

    public function test_owner_can_view_unpublished_event(): void
    {
        $owner = $this->alumniUser(withCreatePermission: true);

        $event = Event::create([
            'user_id' => $owner->id,
            'type' => 'opportunity',
            'eventname' => 'Pending alumni meetup',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Online',
            'is_published' => false,
        ]);

        $response = $this->actingAs($owner)->get(route('alumni.events.show', $event));

        $response->assertOk();
        $response->assertSee('Pending alumni meetup');
        $response->assertSee('Pending review');
        $response->assertDontSee('Share on feed');
    }

    public function test_other_alumni_cannot_view_unpublished_event(): void
    {
        $owner = $this->alumniUser(withCreatePermission: true);
        $viewer = $this->alumniUser(withCreatePermission: false);

        $event = Event::create([
            'user_id' => $owner->id,
            'type' => 'opportunity',
            'eventname' => 'Private draft event',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Online',
            'is_published' => false,
        ]);

        $response = $this->actingAs($viewer)->get(route('alumni.events.show', $event));

        $response->assertForbidden();
    }

    public function test_published_community_event_appears_on_discover_events_tab(): void
    {
        $owner = $this->alumniUser(withCreatePermission: true);
        $viewer = $this->alumniUser(withCreatePermission: false);

        Event::create([
            'user_id' => $owner->id,
            'type' => 'opportunity',
            'eventname' => 'Published community gala',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Senate Hall',
            'is_published' => true,
        ]);

        $response = $this->actingAs($viewer)->get(route('alumni.discover', ['tab' => 'events']));

        $response->assertOk();
        $response->assertSee('Published community gala');
    }

    public function test_unpublished_community_event_does_not_appear_on_discover(): void
    {
        $owner = $this->alumniUser(withCreatePermission: true);
        $viewer = $this->alumniUser(withCreatePermission: false);

        Event::create([
            'user_id' => $owner->id,
            'type' => 'opportunity',
            'eventname' => 'Hidden draft gala',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Senate Hall',
            'is_published' => false,
        ]);

        $response = $this->actingAs($viewer)->get(route('alumni.discover', ['tab' => 'events']));

        $response->assertOk();
        $response->assertDontSee('Hidden draft gala');
    }

    public function test_my_events_page_lists_creator_events(): void
    {
        $user = $this->alumniUser(withCreatePermission: true);

        Event::create([
            'user_id' => $user->id,
            'type' => 'opportunity',
            'eventname' => 'Networking brunch',
            'date' => now()->addWeek()->toDateString(),
            'venue' => 'Campus cafe',
            'is_published' => false,
        ]);

        $response = $this->actingAs($user)->get(route('alumni.events.mine'));

        $response->assertOk();
        $response->assertSee('Networking brunch');
        $response->assertSee('Pending review');
    }

    private function alumniUser(bool $withCreatePermission): User
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $permissions = ['view events'];

        if ($withCreatePermission) {
            $permissions[] = 'create event';
        }

        $user->givePermissionTo($permissions);

        return $user;
    }
}
