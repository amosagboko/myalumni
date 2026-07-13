<?php



namespace Tests\Feature\Alumni;



use App\Models\Alumni;

use App\Models\Candidate;

use App\Models\Election;

use App\Models\ElectionOffice;

use App\Models\FeeTemplate;

use App\Models\FeeType;

use App\Models\User;

use App\Services\AlumniDuesService;

use Illuminate\Foundation\Testing\DatabaseTransactions;

use Spatie\Permission\Models\Role;

use Tests\TestCase;



class ElectionEoiFormPageTest extends TestCase

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



    private function createOpenEoiElection(): array

    {

        $election = Election::create([

            'title' => 'EOI Form Election 2026',

            'election_year' => 2026,

            'description' => 'Election accepting EOI applications.',

            'eoi_start' => now()->subDay(),

            'eoi_end' => now()->addWeek(),

            'accreditation_start' => now()->addWeeks(2),

            'accreditation_end' => now()->addWeeks(3),

            'voting_start' => now()->addWeeks(4),

            'voting_end' => now()->addWeeks(5),

            'status' => 'eoi',

            'is_active' => true,

            'screening_fee' => 5000,

        ]);



        $feeType = FeeType::firstOrCreate(

            ['code' => 'screening'],

            [

                'name' => 'Screening Fee',

                'description' => 'Election candidate screening fee',

                'is_active' => true,

                'is_system' => false,

            ]

        );



        $office = ElectionOffice::create([

            'election_id' => $election->id,

            'title' => 'Secretary',

            'description' => 'Handles association documentation.',

            'max_candidates' => 3,

            'fee_type_id' => $feeType->id,

        ]);



        FeeTemplate::create([
            'fee_type_id' => $feeType->id,
            'graduation_year' => FeeTemplate::PAYMENT_YEAR_ALL,
            'amount' => 5000,
            'description' => 'EOI screening fee',
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => null,
        ]);



        return [$election, $office];

    }



    public function test_eoi_form_redirects_when_eoi_period_closed(): void

    {

        $this->allowPortalAccess();



        $user = User::factory()->create();

        $user->assignRole('alumni');

        $this->createAlumni($user);



        $election = Election::create([

            'title' => 'Closed EOI Election',

            'election_year' => 2026,

            'description' => 'EOI already ended.',

            'eoi_start' => now()->subMonth(),

            'eoi_end' => now()->subWeek(),

            'accreditation_start' => now()->addWeek(),

            'accreditation_end' => now()->addWeeks(2),

            'voting_start' => now()->addWeeks(3),

            'voting_end' => now()->addWeeks(4),

            'status' => 'eoi_closed',

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

            'title' => 'Treasurer',

            'description' => 'Manages finances.',

            'max_candidates' => 2,

            'fee_type_id' => $feeType->id,

        ]);



        $this->actingAs($user->fresh(['alumni']))

            ->get(route('alumni.elections.expression-of-interest.form', [$election, $office]))

            ->assertRedirect(route('alumni.elections'));

    }



    public function test_eoi_form_page_renders_for_eligible_alumni(): void

    {

        $this->allowPortalAccess();



        $user = User::factory()->create();

        $user->assignRole('alumni');

        $this->createAlumni($user);



        [$election, $office] = $this->createOpenEoiElection();



        $response = $this->actingAs($user->fresh(['alumni']))

            ->get(route('alumni.elections.expression-of-interest.form', [$election, $office]));



        $response->assertOk();

        $response->assertSee('Expression of Interest');

        $response->assertSee('EOI Form Election 2026');

        $response->assertSee('Secretary');

        $response->assertSee('Handles association documentation.');

        $response->assertSee('Preview application');

        $response->assertSee('elections-eoi-form-page', false);

    }

    public function test_eoi_form_returns_not_found_when_office_does_not_belong_to_election(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        [$electionA, $_officeA] = $this->createOpenEoiElection();
        [$electionB, $officeB] = $this->createOpenEoiElection();

        $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.elections.expression-of-interest.form', [$electionA, $officeB]))
            ->assertNotFound();
    }

}

