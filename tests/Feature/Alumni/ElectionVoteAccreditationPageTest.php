<?php

namespace Tests\Feature\Alumni;

use App\Models\AccreditedVoter;
use App\Models\Alumni;
use App\Models\Election;
use App\Models\User;
use App\Services\AlumniDuesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ElectionVoteAccreditationPageTest extends TestCase
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

    private function createAccreditationElection(): Election
    {
        return Election::create([
            'title' => 'Accreditation Phase Election',
            'election_year' => 2026,
            'description' => 'Election in accreditation phase.',
            'eoi_start' => now()->subMonth(),
            'eoi_end' => now()->subWeeks(3),
            'accreditation_start' => now()->subDay(),
            'accreditation_end' => now()->addWeek(),
            'voting_start' => now()->addWeeks(2),
            'voting_end' => now()->addWeeks(3),
            'status' => 'accreditation',
            'is_active' => true,
            'screening_fee' => 5000,
        ]);
    }

    private function createVotingElection(): Election
    {
        return Election::create([
            'title' => 'Voting Phase Election',
            'election_year' => 2026,
            'description' => 'Election in voting phase.',
            'eoi_start' => now()->subMonth(),
            'eoi_end' => now()->subWeeks(3),
            'accreditation_start' => now()->subWeeks(2),
            'accreditation_end' => now()->subWeek(),
            'voting_start' => now()->subDay(),
            'voting_end' => now()->addWeek(),
            'status' => 'voting',
            'is_active' => true,
            'screening_fee' => 5000,
        ]);
    }

    public function test_accreditation_page_renders_for_active_window(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        $election = $this->createAccreditationElection();

        $response = $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.elections.accreditation', $election));

        $response->assertOk();
        $response->assertSee('Accreditation');
        $response->assertSee('Accreditation Phase Election');
        $response->assertSee('Submit accreditation request');
        $response->assertSee('elections-accreditation-page', false);
    }

    public function test_vote_page_redirects_when_dues_unpaid(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        $election = $this->createVotingElection();

        $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.elections.vote', $election))
            ->assertRedirect(route('alumni.payments.index'));
    }

    public function test_vote_page_prompts_accreditation_when_not_accredited(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        $election = $this->createVotingElection();

        $response = $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.elections.vote', $election));

        $response->assertOk();
        $response->assertSee('Cast Your Vote');
        $response->assertSee('You are not accredited for this election');
        $response->assertSee(route('alumni.elections.accreditation', $election), false);
    }

    public function test_vote_page_shows_ballot_for_accredited_voter(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $alumni = $this->createAlumni($user);

        $election = $this->createVotingElection();

        AccreditedVoter::create([
            'election_id' => $election->id,
            'alumni_id' => $alumni->id,
            'has_voted' => false,
            'accredited_at' => now(),
        ]);

        $response = $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.elections.vote', $election));

        $response->assertOk();
        $response->assertSee('Preview votes');
        $response->assertSee('elections-vote-page', false);
        $response->assertDontSee('You are not accredited for this election');
    }
}
