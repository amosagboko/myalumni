<?php

namespace Tests\Feature\Social;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConnectionsPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    public function test_connections_page_renders_for_alumni(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $response = $this->actingAs($user)->get(route('friends'));

        $response->assertOk();
        $response->assertSee('Connections');
        $response->assertSee('My Connections');
        $response->assertSee('People You May Know');
        $response->assertSee('search-form-2', false);
        $response->assertSee('connections-page', false);
        $response->assertSee('wire:snapshot', false);
    }
}
