<?php



namespace Tests\Feature\Alumni;



use App\Models\Alumni;

use App\Models\Candidate;

use App\Models\Election;

use App\Models\ElectionOffice;

use App\Models\FeeType;

use App\Models\User;

use App\Services\AlumniDuesService;

use Illuminate\Foundation\Testing\DatabaseTransactions;

use Spatie\Permission\Models\Role;

use Tests\TestCase;



class ElectionPublishedCandidatesPageTest extends TestCase

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



    private function createAlumni(User $user, string $matric): Alumni

    {

        return Alumni::create([

            'user_id' => $user->id,

            'matric_number' => $matric,

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



    public function test_published_candidates_page_shows_empty_state(): void

    {

        $this->allowPortalAccess();



        $viewer = User::factory()->create(['name' => 'Viewer Alumni']);

        $viewer->assignRole('alumni');

        $this->createAlumni($viewer, 'MAT-VIEW-001');



        $election = Election::create([

            'title' => 'Published Candidates Election',

            'election_year' => 2026,

            'description' => 'Election with approved candidates.',

            'eoi_start' => now()->subMonth(),

            'eoi_end' => now()->subWeeks(3),

            'accreditation_start' => now()->subWeeks(2),

            'accreditation_end' => now()->subWeek(),

            'voting_start' => now()->addWeek(),

            'voting_end' => now()->addWeeks(2),

            'status' => 'accreditation',

            'is_active' => true,

            'screening_fee' => 5000,

        ]);



        $feeType = FeeType::firstOrCreate(['code' => 'screening'], [

            'name' => 'Screening Fee',

            'description' => 'Election candidate screening fee',

            'is_active' => true,

            'is_system' => false,

        ]);



        $office = ElectionOffice::create([

            'election_id' => $election->id,

            'title' => 'President',

            'description' => 'Lead the alumni association.',

            'max_candidates' => 5,

            'fee_type_id' => $feeType->id,

        ]);



        $response = $this->actingAs($viewer->fresh(['alumni']))

            ->get(route('alumni.elections.published-candidates', [$election, $office]));



        $response->assertOk();

        $response->assertSee('Published Candidates');

        $response->assertSee('No candidates have been published for this office yet.');

        $response->assertSee('elections-published-candidates-page', false);

    }



    public function test_published_candidates_page_lists_approved_candidates(): void

    {

        $this->allowPortalAccess();



        $viewer = User::factory()->create(['name' => 'Viewer Alumni']);

        $viewer->assignRole('alumni');

        $this->createAlumni($viewer, 'MAT-VIEW-002');



        $candidateUser = User::factory()->create(['name' => 'Jane Candidate']);

        $candidateUser->assignRole('alumni');

        $candidateAlumni = $this->createAlumni($candidateUser, 'MAT-CAND-001');



        $election = Election::create([

            'title' => 'Published Candidates Election',

            'election_year' => 2026,

            'description' => 'Election with approved candidates.',

            'eoi_start' => now()->subMonth(),

            'eoi_end' => now()->subWeeks(3),

            'accreditation_start' => now()->subWeeks(2),

            'accreditation_end' => now()->subWeek(),

            'voting_start' => now()->addWeek(),

            'voting_end' => now()->addWeeks(2),

            'status' => 'accreditation',

            'is_active' => true,

            'screening_fee' => 5000,

        ]);



        $feeType = FeeType::firstOrCreate(['code' => 'screening'], [

            'name' => 'Screening Fee',

            'description' => 'Election candidate screening fee',

            'is_active' => true,

            'is_system' => false,

        ]);



        $office = ElectionOffice::create([

            'election_id' => $election->id,

            'title' => 'President',

            'description' => 'Lead the alumni association.',

            'max_candidates' => 5,

            'fee_type_id' => $feeType->id,

        ]);



        Candidate::create([

            'election_id' => $election->id,

            'election_office_id' => $office->id,

            'alumni_id' => $candidateAlumni->id,

            'status' => Candidate::STATUS_APPROVED,

            'has_paid_screening_fee' => true,

            'manifesto' => 'I will champion transparency and alumni engagement across all faculties.',

            'passport' => 'passports/sample.jpg',

        ]);



        $response = $this->actingAs($viewer->fresh(['alumni']))

            ->get(route('alumni.elections.published-candidates', [$election, $office]));



        $response->assertOk();

        $response->assertSee('Jane Candidate');

        $response->assertSee('MAT-CAND-001');

        $response->assertSee('I will champion transparency');

        $response->assertSee('View manifesto');

        $response->assertDontSee('matriculation_number');

    }

    public function test_published_candidates_returns_not_found_when_office_does_not_belong_to_election(): void
    {
        $this->allowPortalAccess();

        $viewer = User::factory()->create(['name' => 'Viewer Alumni']);
        $viewer->assignRole('alumni');
        $this->createAlumni($viewer, 'MAT-VIEW-003');

        $feeType = FeeType::firstOrCreate(['code' => 'screening'], [
            'name' => 'Screening Fee',
            'description' => 'Election candidate screening fee',
            'is_active' => true,
            'is_system' => false,
        ]);

        $electionA = Election::create([
            'title' => 'Election A',
            'election_year' => 2026,
            'description' => 'First election.',
            'eoi_start' => now()->subMonth(),
            'eoi_end' => now()->subWeeks(3),
            'accreditation_start' => now()->subWeeks(2),
            'accreditation_end' => now()->subWeek(),
            'voting_start' => now()->addWeek(),
            'voting_end' => now()->addWeeks(2),
            'status' => 'accreditation',
            'is_active' => true,
            'screening_fee' => 5000,
        ]);

        $electionB = Election::create([
            'title' => 'Election B',
            'election_year' => 2026,
            'description' => 'Second election.',
            'eoi_start' => now()->subMonth(),
            'eoi_end' => now()->subWeeks(3),
            'accreditation_start' => now()->subWeeks(2),
            'accreditation_end' => now()->subWeek(),
            'voting_start' => now()->addWeek(),
            'voting_end' => now()->addWeeks(2),
            'status' => 'accreditation',
            'is_active' => true,
            'screening_fee' => 5000,
        ]);

        $officeB = ElectionOffice::create([
            'election_id' => $electionB->id,
            'title' => 'President',
            'description' => 'Lead the alumni association.',
            'max_candidates' => 5,
            'fee_type_id' => $feeType->id,
        ]);

        $this->actingAs($viewer->fresh(['alumni']))
            ->get(route('alumni.elections.published-candidates', [$electionA, $officeB]))
            ->assertNotFound();
    }

}

