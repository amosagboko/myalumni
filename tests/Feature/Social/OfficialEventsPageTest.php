<?php

namespace Tests\Feature\Social;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OfficialEventsPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    public function test_official_events_page_renders_for_alumni(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $response = $this->actingAs($user)->get(route('alumni.events'));

        $response->assertOk();
        $response->assertSee('Official Events');
        $response->assertSee('Upcoming');
        $response->assertSee('Coming Up');
        $response->assertSee('events-page', false);
        $response->assertSee('wire:snapshot', false);
    }
}
