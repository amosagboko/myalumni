<?php

namespace Tests\Feature\Social;

use App\Models\Alumni;
use App\Models\Post;
use App\Models\User;
use App\Services\Social\FeedService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use App\Livewire\Social\AlumniProfileShow;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlumniProfilePageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    public function test_alumni_can_view_own_public_profile(): void
    {
        $user = $this->createAlumniUser('James Ako', 'Science', 2018);

        $response = $this->actingAs($user)->get(route('alumni.members.show', $user));

        $response->assertOk();
        $response->assertSee('James Ako');
        $response->assertSee('Class of 2018');
        $response->assertSee('Science');
        $response->assertSee('Edit Settings');
        $response->assertSee('alumni-profile-page', false);
        $response->assertSee('wire:snapshot', false);

        if (! config('social.realtime_enabled')) {
            $response->assertSee('wire:poll.visible.30s', false);
        }
    }

    public function test_alumni_can_view_another_alumni_profile_and_connect(): void
    {
        $viewer = $this->createAlumniUser('Viewer User', 'Arts', 2019);
        $profile = $this->createAlumniUser('Profile Owner', 'Science', 2020);

        $response = $this->actingAs($viewer)->get(route('alumni.members.show', $profile));

        $response->assertOk();
        $response->assertSee('Profile Owner');
        $response->assertSee('Class of 2020 · Science');
        $response->assertSee('Connect');

        Livewire::actingAs($viewer)
            ->test(AlumniProfileShow::class, ['user' => $profile->id])
            ->call('sendRequest', $profile->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('friend_requests', [
            'sender_id' => $viewer->id,
            'receiver_id' => $profile->id,
            'status' => 'pending',
        ]);
    }

    public function test_profile_shows_visible_posts_to_viewer(): void
    {
        $viewer = $this->createAlumniUser('Viewer User', 'Arts', 2019);
        $profile = $this->createAlumniUser('Profile Owner', 'Science', 2020);

        Post::factory()->create([
            'user_id' => $profile->id,
            'content' => 'Hello all alumni from profile page',
            'visibility' => FeedService::VISIBILITY_ALL_ALUMNI,
            'status' => 'published',
        ]);

        $response = $this->actingAs($viewer)->get(route('alumni.members.show', $profile));

        $response->assertOk();
        $response->assertSee('Hello all alumni from profile page');
    }

    public function test_non_alumni_profile_returns_not_found(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('alumni');

        $other = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('alumni.members.show', $other))
            ->assertNotFound();
    }

    protected function createAlumniUser(string $name, string $faculty, int $year): User
    {
        $user = User::factory()->create(['name' => $name]);
        $user->assignRole('alumni');

        Alumni::create([
            'user_id' => $user->id,
            'matric_number' => 'MAT'.uniqid(),
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'faculty' => $faculty,
            'year_of_graduation' => $year,
            'date_of_birth' => '1995-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => $year - 4,
            'gender' => 'male',
        ]);

        return $user->fresh(['alumni']);
    }
}
