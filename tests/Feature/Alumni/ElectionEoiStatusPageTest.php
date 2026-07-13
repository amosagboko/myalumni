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

class ElectionEoiStatusPageTest extends TestCase
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

    public function test_eoi_status_redirects_when_no_application_exists(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.elections.expression-of-interest.status'))
            ->assertRedirect(route('alumni.elections'));
    }

    public function test_eoi_status_redirects_to_form_when_eoi_is_open(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        $election = Election::create([
            'title' => 'Open EOI Election',
            'election_year' => 2026,
            'description' => 'EOI currently open.',
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
            ->get(route('alumni.elections.expression-of-interest.status'))
            ->assertRedirect(route('alumni.elections.expression-of-interest.form', [$election, $office]));
    }

    public function test_eoi_status_does_not_redirect_to_form_when_eoi_closed(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $this->createAlumni($user);

        $election = Election::create([
            'title' => 'Closed EOI Election',
            'election_year' => 2026,
            'description' => 'EOI closed, no application on file.',
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

        ElectionOffice::create([
            'election_id' => $election->id,
            'title' => 'Treasurer',
            'description' => 'Manages finances.',
            'max_candidates' => 2,
            'fee_type_id' => $feeType->id,
        ]);

        $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.elections.expression-of-interest.status'))
            ->assertRedirect(route('alumni.elections'));
    }

    public function test_eoi_status_page_renders_application_details(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');
        $alumni = $this->createAlumni($user);

        $election = Election::create([
            'title' => 'EOI Election 2026',
            'election_year' => 2026,
            'description' => 'Election with open EOI.',
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
            'title' => 'President',
            'description' => 'Lead the alumni association.',
            'max_candidates' => 5,
            'fee_type_id' => $feeType->id,
        ]);

        Candidate::create([
            'election_id' => $election->id,
            'election_office_id' => $office->id,
            'alumni_id' => $alumni->id,
            'status' => Candidate::STATUS_PAID_AWAITING_SCREENING,
            'has_paid_screening_fee' => true,
            'manifesto' => 'I will serve with integrity and transparency.',
            'passport' => 'passports/sample.jpg',
        ]);

        $response = $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.elections.expression-of-interest.status'));

        $response->assertOk();
        $response->assertSee('EOI Status');
        $response->assertSee('EOI Election 2026');
        $response->assertSee('President');
        $response->assertSee('Paid, awaiting ELCOM screening');
        $response->assertSee('I will serve with integrity and transparency.');
        $response->assertSee('elections-eoi-status-page', false);
        $response->assertSee('Suggest agent');
    }
}
