<?php

namespace Tests\Feature\Alumni;

use App\Models\Alumni;
use App\Models\AlumniYear;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Alumni\AlumniMemberAccessService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlumniMemberAccessTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['alumni', 'alumni-president', 'elcom-chairman'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_service_requires_biodata_cohort_fees_and_annual_due(): void
    {
        $service = app(AlumniMemberAccessService::class);

        $alumni = $this->createAlumniWithRole('alumni', completeBioData: false);
        $this->assertFalse($service->isFullMember($alumni));

        $alumni->update([
            'contact_address' => '12 Alumni Street',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);
        $this->seedLegacySubscription($alumni->year_of_graduation);
        $this->assertFalse($service->isFullMember($alumni->fresh()));

        $this->paySubscriptionFor($alumni);
        $this->assertTrue($service->isFullMember($alumni->fresh()));

        $this->seedActiveAnnualDue(2026);
        $this->assertFalse($service->isFullMember($alumni->fresh()));
    }

    public function test_limited_alumni_redirected_from_newsfeed_to_bio_data(): void
    {
        $alumni = $this->createAlumniWithRole('alumni', completeBioData: false);

        $response = $this->actingAs($alumni->user)->get(route('alumni.home'));

        $response->assertRedirect(route('alumni.bio-data'));
    }

    public function test_limited_alumni_redirected_from_newsfeed_to_payments(): void
    {
        $alumni = $this->createAlumniWithRole('alumni');
        $this->seedLegacySubscription($alumni->year_of_graduation);

        $response = $this->actingAs($alumni->user)->get(route('alumni.home'));

        $response->assertRedirect(route('alumni.payments.index'));
    }

    public function test_full_member_can_access_newsfeed(): void
    {
        $alumni = $this->createAlumniWithRole('alumni');
        $this->seedLegacySubscription($alumni->year_of_graduation);
        $this->paySubscriptionFor($alumni);

        $response = $this->actingAs($alumni->user)->get(route('alumni.home'));

        $response->assertOk();
    }

    public function test_alumni_president_uses_same_member_gate(): void
    {
        $alumni = $this->createAlumniWithRole('alumni-president', completeBioData: false);

        $response = $this->actingAs($alumni->user)->get(route('alumni.home'));

        $response->assertRedirect(route('alumni.bio-data'));
    }

    public function test_operational_dashboard_skips_member_gate(): void
    {
        $alumni = $this->createAlumniWithRole('elcom-chairman');
        $alumni->user->assignRole('alumni');

        $response = $this->actingAs($alumni->user)->get(route('elcom-chairman.dashboard'));

        $response->assertOk();
    }

    public function test_limited_member_can_still_view_clearance_status(): void
    {
        $alumni = $this->createAlumniWithRole('alumni', completeBioData: false);

        $response = $this->actingAs($alumni->user)->get(route('alumni.clearance-status'));

        $response->assertOk();
    }

    private function createAlumniWithRole(string $role, bool $completeBioData = true): Alumni
    {
        $user = User::factory()->create();
        $user->assignRole($role);

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
            'contact_address' => $completeBioData ? '12 Alumni Street, Lafia' : null,
            'phone_number' => $completeBioData ? '08012345678' : null,
            'qualification_type' => $completeBioData ? 'B.Sc' : null,
        ]);
    }

    private function seedLegacySubscription(int $graduationYear): FeeTemplate
    {
        $subscriptionType = FeeType::firstOrCreate(
            ['code' => 'subscription'],
            [
                'name' => 'Subscription Registration Fee',
                'description' => 'Initial alumni subscription',
                'is_active' => true,
                'is_system' => true,
            ]
        );

        return FeeTemplate::create([
            'fee_type_id' => $subscriptionType->id,
            'graduation_year' => $graduationYear,
            'amount' => 2000,
            'description' => 'Alumni subscription',
            'is_active' => true,
            'valid_from' => now()->subYear(),
            'valid_until' => null,
        ]);
    }

    private function paySubscriptionFor(Alumni $alumni): void
    {
        $template = FeeTemplate::where('graduation_year', $alumni->year_of_graduation)->first();
        $this->assertNotNull($template);

        Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $template->id,
            'amount' => $template->amount,
            'status' => 'paid',
            'payment_reference' => 'TEST-'.uniqid(),
            'payment_provider' => 'credocentral',
        ]);
    }

    private function seedActiveAnnualDue(int $year): void
    {
        AlumniYear::create([
            'year' => $year,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);

        $annualType = FeeType::firstOrCreate(
            ['code' => FeeType::ANNUAL_DUE_CODE],
            [
                'name' => 'Annual Alumni Due',
                'description' => 'Yearly renewal due',
                'is_active' => true,
                'is_system' => true,
            ]
        );

        FeeTemplate::create([
            'fee_type_id' => $annualType->id,
            'fee_purpose' => FeeTemplate::PURPOSE_ANNUAL_RENEWAL,
            'graduation_year' => $year,
            'amount' => 5000,
            'description' => "Annual due {$year}",
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => null,
        ]);
    }
}
