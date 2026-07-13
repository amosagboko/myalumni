<?php

namespace Tests\Feature\Alumni;

use App\Models\Alumni;
use App\Models\Election;
use App\Models\User;
use App\Services\AlumniDuesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ElectionResultsPageTest extends TestCase
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

    private function createCompletedElection(): Election
    {
        return Election::create([
            'title' => 'Completed Alumni Election',
            'election_year' => 2025,
            'description' => 'Historical election results.',
            'eoi_start' => now()->subMonths(3),
            'eoi_end' => now()->subMonths(2),
            'accreditation_start' => now()->subMonths(2),
            'accreditation_end' => now()->subMonths(2)->addDays(3),
            'voting_start' => now()->subMonths(2)->addDays(4),
            'voting_end' => now()->subMonth(),
            'status' => 'completed',
            'is_active' => false,
            'screening_fee' => 5000,
        ]);
    }

    public function test_results_page_redirects_when_results_not_published(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        $election = Election::create([
            'title' => 'Draft Election',
            'election_year' => 2026,
            'description' => 'Not yet run.',
            'eoi_start' => now()->addWeek(),
            'eoi_end' => now()->addWeeks(2),
            'accreditation_start' => now()->addWeeks(3),
            'accreditation_end' => now()->addWeeks(4),
            'voting_start' => now()->addWeeks(5),
            'voting_end' => now()->addWeeks(6),
            'status' => 'draft',
            'is_active' => true,
            'screening_fee' => 5000,
        ]);

        $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.elections.results', $election))
            ->assertRedirect(route('alumni.elections'));
    }

    public function test_results_page_renders_for_completed_election(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        $election = $this->createCompletedElection();

        $response = $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.elections.results', $election));

        $response->assertOk();
        $response->assertSee('Election Results');
        $response->assertSee('Completed Alumni Election');
        $response->assertSee('elections-results-page', false);
        $response->assertSee('Results by office');
        $response->assertSee(route('alumni.elections'), false);
    }
}
