<?php

namespace Tests\Feature\Social;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlumniHomeLivewirePageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    public function test_alumni_home_renders_livewire_post_composer_assets(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $response = $this->actingAs($user)->get(route('alumni.home'));

        $response->assertOk();
        $response->assertSee('wire:click.prevent="createPost"', false);
        $response->assertSee('livewire/livewire.js', false);
        $response->assertSee('wire:snapshot', false);
    }
}
