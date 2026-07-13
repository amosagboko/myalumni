<?php

namespace Tests\Feature\Social;

use App\Models\Alumni;
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
        $response->assertSee('wire:poll.visible.30s', false);
        $response->assertSee('livewire/livewire.js', false);
        $response->assertSee('wire:snapshot', false);
    }

    public function test_alumni_home_welcome_card_links_to_profile_with_class_year(): void
    {
        $user = User::factory()->create(['name' => 'Najeeb Hassan']);
        $user->assignRole('alumni');

        Alumni::create([
            'user_id' => $user->id,
            'matric_number' => 'MAT'.uniqid(),
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'faculty' => 'Science',
            'year_of_graduation' => 2020,
            'date_of_birth' => '1995-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2016,
            'gender' => 'male',
        ]);

        $response = $this->actingAs($user->fresh(['alumni']))->get(route('alumni.home'));

        $response->assertOk();
        $response->assertSee('feed-announcements-welcome__avatar-img', false);
        $response->assertSee('Class of 2020');
        $response->assertSee(route('alumni.members.show', $user), false);
    }
}
