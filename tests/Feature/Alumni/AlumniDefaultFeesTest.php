<?php

namespace Tests\Feature\Alumni;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\AlumniYear;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Alumni\ClearanceFormService;
use App\Services\AlumniDuesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlumniDefaultFeesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    private function createLegacyAlumni(int $graduationYear = 2020): Alumni
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        return Alumni::create([
            'user_id' => $user->id,
            'matric_number' => 'MAT'.uniqid(),
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'faculty' => 'Science',
            'year_of_graduation' => $graduationYear,
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

    private function createCohortAlumni(int $graduationYear = 2025): Alumni
    {
        $category = AlumniCategory::firstOrCreate(
            ['slug' => 'undergraduate-full-time'],
            ['name' => 'Undergraduate Full Time', 'description' => 'Undergraduate full time alumni']
        );

        $user = User::factory()->create();
        $user->assignRole('alumni');

        return Alumni::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'matric_number' => 'MAT'.uniqid(),
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'faculty' => 'Science',
            'year_of_graduation' => $graduationYear,
            'date_of_birth' => '2000-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2021,
            'gender' => 'female',
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);
    }

    private function onboardingFeeType(string $code): FeeType
    {
        return FeeType::firstOrCreate(
            ['code' => $code],
            [
                'name' => ucfirst(str_replace('_', ' ', $code)),
                'description' => 'Onboarding fee',
                'is_active' => true,
                'is_system' => true,
            ]
        );
    }

    private function subscriptionFeeType(): FeeType
    {
        return FeeType::firstOrCreate(
            ['code' => 'subscription'],
            [
                'name' => 'Subscription Registration Fee',
                'description' => 'Initial alumni subscription',
                'is_active' => true,
                'is_system' => true,
            ]
        );
    }

    private function annualDueFeeType(): FeeType
    {
        return FeeType::firstOrCreate(
            ['code' => FeeType::ANNUAL_DUE_CODE],
            [
                'name' => 'Annual Alumni Due',
                'description' => 'Yearly renewal due',
                'is_active' => true,
                'is_system' => true,
            ]
        );
    }

    public function test_legacy_alumni_with_unpaid_subscription_has_default_fees_due(): void
    {
        $alumni = $this->createLegacyAlumni(2020);
        $subscriptionType = $this->subscriptionFeeType();

        $subscriptionTemplate = FeeTemplate::create([
            'fee_type_id' => $subscriptionType->id,
            'graduation_year' => 2020,
            'amount' => 2000,
            'description' => 'Alumni subscription fee 2020',
            'is_active' => true,
            'valid_from' => now()->subYear(),
            'valid_until' => null,
        ]);

        $service = app(AlumniDuesService::class);

        $this->assertFalse($service->hasCompletedDefaultFees($alumni));
        $this->assertFalse($alumni->fresh()->hasCompletedDefaultFees());

        $activeFees = $service->getActiveFees($alumni);
        $this->assertCount(1, $activeFees);
        $this->assertTrue($activeFees->first()->is($subscriptionTemplate));
        $this->assertSame(AlumniDuesService::PHASE_ONBOARDING, $service->getDuesPhase($alumni));
    }

    public function test_legacy_alumni_with_paid_subscription_moves_to_annual_due_phase(): void
    {
        $alumni = $this->createLegacyAlumni(2020);
        $subscriptionType = $this->subscriptionFeeType();
        $annualType = $this->annualDueFeeType();

        $subscriptionTemplate = FeeTemplate::create([
            'fee_type_id' => $subscriptionType->id,
            'graduation_year' => 2020,
            'amount' => 2000,
            'description' => 'Alumni subscription fee 2020',
            'is_active' => true,
            'valid_from' => now()->subYear(),
            'valid_until' => null,
        ]);

        $paymentYear = AlumniYear::create([
            'year' => 2026,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);

        $annualTemplate = FeeTemplate::create([
            'fee_type_id' => $annualType->id,
            'fee_purpose' => FeeTemplate::PURPOSE_ANNUAL_RENEWAL,
            'graduation_year' => $paymentYear->year,
            'amount' => 5000,
            'description' => 'Annual due 2026',
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => null,
        ]);

        Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $subscriptionTemplate->id,
            'amount' => 2000,
            'status' => 'paid',
            'payment_reference' => 'SUB-'.uniqid(),
            'payment_provider' => 'credo',
        ]);

        $service = app(AlumniDuesService::class);

        $this->assertTrue($service->hasCompletedDefaultFees($alumni));
        $this->assertSame(AlumniDuesService::PHASE_ANNUAL, $service->getDuesPhase($alumni));

        $activeFees = $service->getActiveFees($alumni);
        $this->assertCount(1, $activeFees);
        $this->assertTrue($activeFees->first()->is($annualTemplate));
    }

    public function test_unpaid_legacy_subscription_triggers_portal_payment_gate(): void
    {
        $alumni = $this->createLegacyAlumni(2020);
        $subscriptionType = $this->subscriptionFeeType();

        FeeTemplate::create([
            'fee_type_id' => $subscriptionType->id,
            'graduation_year' => 2020,
            'amount' => 2000,
            'description' => 'Alumni subscription fee 2020',
            'is_active' => true,
            'valid_from' => now()->subYear(),
            'valid_until' => null,
        ]);

        $status = app(ClearanceFormService::class)->accessStatus($alumni);

        $this->assertTrue($status['needsPayments']);
        $this->assertFalse($status['allOk']);
    }

    public function test_2025_graduate_with_paid_annual_but_pending_subscription_stays_gated(): void
    {
        $cohortYear = 2099;
        $alumni = $this->createCohortAlumni($cohortYear);
        $subscriptionType = $this->subscriptionFeeType();
        $annualType = $this->annualDueFeeType();
        $registrationType = $this->onboardingFeeType('registration');

        $registrationTemplate = FeeTemplate::create([
            'fee_type_id' => $registrationType->id,
            'category_id' => $alumni->category_id,
            'fee_purpose' => FeeTemplate::PURPOSE_ONBOARDING,
            'graduation_year' => $cohortYear,
            'amount' => 10000,
            'description' => 'Registration fee '.$cohortYear,
            'is_active' => true,
            'valid_from' => now()->subYear(),
            'valid_until' => null,
        ]);

        $subscriptionTemplate = FeeTemplate::create([
            'fee_type_id' => $subscriptionType->id,
            'graduation_year' => $cohortYear,
            'amount' => 2000,
            'description' => 'Annual subscription fee '.$cohortYear,
            'is_active' => true,
            'valid_from' => now()->subYear(),
            'valid_until' => null,
        ]);

        AlumniYear::query()->update(['is_active' => false]);

        $paymentYear = AlumniYear::create([
            'year' => 2026,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);

        $annualTemplate = FeeTemplate::create([
            'fee_type_id' => $annualType->id,
            'fee_purpose' => FeeTemplate::PURPOSE_ANNUAL_RENEWAL,
            'graduation_year' => $paymentYear->year,
            'amount' => 5000,
            'description' => 'Annual due 2026',
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => null,
        ]);

        Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $registrationTemplate->id,
            'amount' => 10000,
            'status' => 'paid',
            'payment_reference' => 'REG-'.uniqid(),
            'payment_provider' => 'credo',
        ]);

        Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $subscriptionTemplate->id,
            'amount' => 2000,
            'status' => 'pending',
            'payment_reference' => 'SUB-'.uniqid(),
            'payment_provider' => 'credo',
        ]);

        Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $annualTemplate->id,
            'amount' => 5000,
            'status' => 'paid',
            'payment_reference' => 'ANN-'.uniqid(),
            'payment_provider' => 'credo',
        ]);

        $service = app(AlumniDuesService::class);

        $this->assertFalse($service->hasCompletedDefaultFees($alumni));
        $this->assertSame(AlumniDuesService::PHASE_ONBOARDING, $service->getDuesPhase($alumni));

        $activeFees = $service->getActiveFees($alumni);
        $this->assertCount(1, $activeFees);
        $this->assertTrue($activeFees->first()->is($subscriptionTemplate));

        $status = app(ClearanceFormService::class)->accessStatus($alumni);
        $this->assertTrue($status['needsPayments']);
        $this->assertFalse($status['allOk']);
    }
}
