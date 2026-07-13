<?php

namespace Tests\Feature\Alumni;

use App\Models\Alumni;
use App\Models\Election;
use App\Models\User;
use App\Services\AlumniDuesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ElectionsHubTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    private function allowPortalAccess(): void
    {
        $this->mock(AlumniDuesService::class, function ($mock) {
            $mock->shouldReceive('getActiveFees')->andReturn(collect());
            $mock->shouldReceive('hasCompletedOnboardingFees')->andReturn(true);
            $mock->shouldReceive('getDuesPhase')->andReturn(AlumniDuesService::PHASE_NONE);
        });
    }

    private function createAlumni(User $user): Alumni
    {
        return Alumni::create([
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
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);
    }

    public function test_elections_hub_requires_authentication(): void
    {
        $this->get(route('alumni.elections'))->assertRedirect(route('login'));
    }

    public function test_elections_hub_renders_empty_state_without_active_cycle(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        $response = $this->actingAs($user->fresh(['alumni']))->get(route('alumni.elections'));

        $response->assertOk();
        $response->assertSee('Elections');
        $response->assertSee('There is no active election cycle at the moment.');
        $response->assertSee('Past Elections');
    }

    public function test_elections_hub_shows_current_election_and_phase(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        Election::create([
            'title' => 'Alumni Association Election 2026',
            'election_year' => 2026,
            'cycle_label' => '2026–2028 Cycle',
            'description' => 'General election for alumni association offices.',
            'eoi_start' => now()->subDay(),
            'eoi_end' => now()->addWeek(),
            'accreditation_start' => now()->addWeeks(2),
            'accreditation_end' => now()->addWeeks(3),
            'voting_start' => now()->addWeeks(3),
            'voting_end' => now()->addWeeks(4),
            'status' => 'eoi',
            'is_active' => true,
            'screening_fee' => 5000,
        ]);

        $response = $this->actingAs($user->fresh(['alumni']))->get(route('alumni.elections'));

        $response->assertOk();
        $response->assertSee('Alumni Association Election 2026');
        $response->assertSee('2026–2028 Cycle');
        $response->assertSee('Expression of Interest');
        $response->assertSee('Your participation');
        $response->assertSee('elections-hub-page', false);
    }
}
