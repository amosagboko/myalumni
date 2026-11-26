<?php

namespace Tests\Feature\AcademicAffairs;

use App\Livewire\AcademicAffairs\Clearance;
use App\Livewire\StudentAffairs\Clearance as StudentAffairsClearance;
use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\AlumniYear;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\PaymentStructure;
use App\Models\PaymentStructureItem;
use App\Models\TransactionItem;
use App\Models\OnboardingSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClearancePaymentStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('academic-affairs');
        Role::findOrCreate('student-affairs');
        Role::findOrCreate('alumni');
        Permission::findOrCreate('toggle academic affairs clearance');

        FeeType::where('code', 'subscription')->update(['is_active' => false]);
    }

    public function test_unpaid_onboarding_fees_are_not_treated_as_paid(): void
    {
        [$alumni] = $this->createUnpaidUgAlumni('2021/SC/PSB/TEST-UNPAID');

        $this->assertFalse($alumni->hasCompletedRequiredPayments());
        $this->assertFalse($alumni->hasPaidAllActiveFees());
        $this->assertTrue($alumni->biodata_completed);
    }

    public function test_paid_onboarding_fees_are_treated_as_paid(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2021/SC/PSB/TEST-PAID');
        $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);

        $this->assertTrue($alumni->fresh()->hasCompletedRequiredPayments());
        $this->assertTrue($alumni->fresh()->hasPaidAllActiveFees());
    }

    public function test_fee_added_after_payment_stays_unpaid_and_is_named(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2021/SC/PSB/LATE-FEE');
        $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);
        $alumni->update(['academic_affairs_cleared' => true]);

        $lateType = FeeType::firstOrCreate(
            ['code' => 'data_processing'],
            ['name' => 'Data Processing', 'description' => 'Data processing', 'is_active' => true, 'is_system' => true]
        );
        $lateFee = $this->createFee($lateType, $alumni->category_id, 2500, 'Late data processing LATE-FEE', 2026);

        $this->assertFalse($alumni->fresh()->hasCompletedRequiredPayments());
        $this->assertTrue((bool) $alumni->fresh()->academic_affairs_cleared);

        Livewire::actingAs($this->academicAffairsUser())
            ->test(Clearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSee('Unpaid')
            ->assertSee('Required payments are not complete;')
            ->assertSee($lateFee->description)
            ->assertSee('pending.')
            ->assertSee('Cleared')
            ->call('viewPaymentDetails', $alumni->id)
            ->assertSee('Pending fees')
            ->assertSee($lateFee->description)
            ->assertSee($registration->description)
            ->assertSee($levy->description)
            ->assertSee('Paid')
            ->assertSee('Pending');
    }

    public function test_added_cohort_fee_without_a_purpose_is_named_as_pending(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2021/SC/PSB/ASSOC-FEE');
        $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);

        $type = FeeType::firstOrCreate(
            ['code' => 'alumni-association-registration'],
            ['name' => 'Alumni Association Registration', 'description' => 'Alumni Association Registration', 'is_active' => true, 'is_system' => false]
        );
        FeeTemplate::create([
            'fee_type_id' => $type->id,
            'category_id' => $alumni->category_id,
            'fee_purpose' => null,
            'graduation_year' => 2026,
            'amount' => 3000,
            'description' => null,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => null,
        ]);

        Livewire::actingAs($this->academicAffairsUser())
            ->test(Clearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSee('Required payments are not complete;')
            ->assertSee('Alumni Association Registration')
            ->assertSee('pending.')
            ->call('viewPaymentDetails', $alumni->id)
            ->assertSee('Paid')
            ->assertSee($registration->description)
            ->assertSee('Pending fees')
            ->assertSee('Alumni Association Registration')
            ->assertSee('Pending');
    }

    public function test_combined_fee_added_after_payment_does_not_reopen_clearance(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2021/SC/PSB/LATE-COMBINED');
        $payment = Transaction::create([
            'alumni_id' => $alumni->id,
            'amount' => $registration->amount + $levy->amount,
            'status' => 'paid',
            'payment_reference' => 'ALUMNI-'.uniqid(),
            'payment_provider' => 'credocentral',
            'paid_at' => now()->subDays(10),
        ]);
        foreach ([$registration, $levy] as $fee) {
            TransactionItem::create([
                'transaction_id' => $payment->id,
                'fee_template_id' => $fee->id,
                'fee_type_id' => $fee->fee_type_id,
                'category_id' => $fee->category_id,
                'description' => $fee->description,
                'amount' => $fee->amount,
            ]);
        }

        $added = $this->associationFee($alumni, now()->subHours(2));
        $this->attachCombinedFee($alumni, $added, now());

        $officer = $this->academicAffairsUser();
        $officer->givePermissionTo('toggle academic affairs clearance');
        $this->enableAcademicAffairsClearance();

        Livewire::actingAs($officer)
            ->test(Clearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSee('Paid')
            ->assertDontSee('Unpaid')
            ->assertDontSee('Alumni Association Registration')
            ->call('markCleared', $alumni->id);

        $this->assertTrue((bool) $alumni->fresh()->academic_affairs_cleared);
    }

    public function test_combined_fee_already_in_the_cohort_keeps_clearance_closed(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2021/SC/PSB/EARLY-COMBINED');
        $added = $this->associationFee($alumni, now()->subDays(3));
        $this->attachCombinedFee($alumni, $added, now()->subDays(10));
        $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);

        $officer = $this->academicAffairsUser();
        $officer->givePermissionTo('toggle academic affairs clearance');
        $this->enableAcademicAffairsClearance();

        Livewire::actingAs($officer)
            ->test(Clearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSee('Unpaid')
            ->assertSee('Alumni Association Registration')
            ->call('markCleared', $alumni->id);

        $this->assertFalse((bool) $alumni->fresh()->academic_affairs_cleared);
        $this->assertTrue($alumni->fresh()->hasCompletedRequiredPayments());
    }

    public function test_paid_cohort_with_no_subscription_fee_is_complete_and_an_active_subscription_still_blocks(): void
    {
        FeeType::where('code', 'subscription')->update(['is_active' => true]);

        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2021/SC/PHY/NO-SUB');
        $alumni->update(['year_of_graduation' => 2039]);
        $registration->update(['graduation_year' => 2039]);
        $levy->update(['graduation_year' => 2039]);
        $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);

        $alumni = $alumni->fresh();
        $service = app(\App\Services\Alumni\ClearancePaymentInspectionService::class);

        $this->assertTrue($alumni->hasCompletedRequiredPayments());
        $this->assertTrue($service->paymentColumn($alumni)['paid']);

        $subscription = FeeType::where('code', 'subscription')->first();
        FeeTemplate::create([
            'fee_type_id' => $subscription->id,
            'category_id' => null,
            'fee_purpose' => FeeTemplate::PURPOSE_ANNUAL_RENEWAL,
            'graduation_year' => 2039,
            'amount' => 2000,
            'description' => 'Subscription fee 2039',
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => null,
        ]);

        $alumni = $alumni->fresh();
        $this->assertFalse($alumni->hasCompletedRequiredPayments());
        $this->assertFalse($service->paymentColumn($alumni)['paid']);
    }

    public function test_paid_required_fees_stay_paid_when_the_annual_due_is_unpaid(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2021/SC/PSB/ANNUAL-DUE');
        $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);

        $paymentYear = AlumniYear::where('is_active', true)->first() ?? AlumniYear::create([
            'year' => 2026,
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_active' => true,
        ]);
        $annualType = FeeType::firstOrCreate(
            ['code' => FeeType::ANNUAL_DUE_CODE],
            ['name' => 'Annual Due', 'description' => 'Annual due', 'is_active' => true, 'is_system' => true]
        );
        $annualDue = FeeTemplate::create([
            'fee_type_id' => $annualType->id,
            'category_id' => null,
            'fee_purpose' => FeeTemplate::PURPOSE_ANNUAL_RENEWAL,
            'graduation_year' => $paymentYear->year,
            'amount' => 5000,
            'description' => 'Annual alumni due for clearance column',
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => null,
        ]);
        Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $annualDue->id,
            'amount' => $annualDue->amount,
            'status' => 'pending',
            'payment_reference' => 'DUE-'.uniqid(),
            'payment_provider' => 'credocentral',
        ]);

        $this->assertTrue($alumni->fresh()->hasCompletedRequiredPayments());

        $officer = $this->academicAffairsUser();
        $officer->givePermissionTo('toggle academic affairs clearance');
        OnboardingSetting::unguarded(function () {
            OnboardingSetting::updateOrCreate(
                ['id' => 1],
                ['is_academic_affairs_clearance_enabled' => true]
            );
        });

        Livewire::actingAs($officer)
            ->test(Clearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSee('Paid')
            ->assertDontSee('Unpaid')
            ->assertDontSee($annualDue->description)
            ->call('viewPaymentDetails', $alumni->id)
            ->assertSee('Paid')
            ->assertSee('No outstanding fees.')
            ->assertDontSee($annualDue->description)
            ->call('closePaymentDetails')
            ->call('markCleared', $alumni->id);

        $this->assertTrue((bool) $alumni->fresh()->academic_affairs_cleared);
    }

    public function test_academic_affairs_clearance_shows_unpaid_for_new_onboarder(): void
    {
        [$alumni] = $this->createUnpaidUgAlumni('2021/SC/PSB/0014-TEST');
        $officer = $this->academicAffairsUser();

        Livewire::actingAs($officer)
            ->test(Clearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSee($alumni->matric_number)
            ->assertSee('Unpaid');
    }

    public function test_academic_affairs_cannot_clear_an_unpaid_alumni(): void
    {
        [$alumni] = $this->createUnpaidUgAlumni('2021/SC/PSB/TEST-CLEAR');
        $officer = $this->academicAffairsUser();
        $officer->givePermissionTo('toggle academic affairs clearance');

        OnboardingSetting::unguarded(function () {
            OnboardingSetting::updateOrCreate(
                ['id' => 1],
                ['is_academic_affairs_clearance_enabled' => true]
            );
        });

        $this->actingAs($officer)
            ->from(route('academic-affairs.clearance'))
            ->post(route('academic-affairs.clearance.toggle', $alumni->id), ['value' => '1'])
            ->assertRedirect(route('academic-affairs.clearance'));

        $this->assertFalse((bool) $alumni->fresh()->academic_affairs_cleared);
        $this->assertFalse($alumni->fresh()->hasCompletedRequiredPayments());
    }

    public function test_academic_affairs_payment_modal_shows_only_the_selected_alumni_transactions(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2021/SC/PSB/MODAL-PAID');
        $payment = $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);

        $other = $this->createAlumni($alumni->category, [
            'matric_number' => '2021/SC/PSB/MODAL-OTHER',
            'year_of_graduation' => 2026,
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);
        $otherPayment = $this->payFee($other, $registration);

        $paidAt = $payment->paid_at->format('d M Y, H:i');

        Livewire::actingAs($this->academicAffairsUser())
            ->test(Clearance::class)
            ->call('viewPaymentDetails', $alumni->id)
            ->assertSee($alumni->user->name)
            ->assertSee($alumni->matric_number)
            ->assertSee('Registration UG FT')
            ->assertSee('5,000.00')
            ->assertSee($payment->payment_reference)
            ->assertSee($paidAt)
            ->assertSee('Paid')
            ->assertDontSee($otherPayment->payment_reference)
            ->call('closePaymentDetails')
            ->assertDontSee($payment->payment_reference);
    }

    public function test_academic_affairs_payment_modal_shows_outstanding_fees(): void
    {
        [$alumni, $registration] = $this->createUnpaidUgAlumni('2021/SC/PSB/MODAL-UNPAID');

        Livewire::actingAs($this->academicAffairsUser())
            ->test(Clearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSee('View Details')
            ->call('viewPaymentDetails', $alumni->id)
            ->assertSee($alumni->matric_number)
            ->assertSee($registration->description)
            ->assertSee('5,000.00')
            ->assertSee('Amount expected')
            ->assertSee('Amount outstanding')
            ->assertSee('Unpaid');
    }

    public function test_student_affairs_payment_modal_shows_only_the_selected_alumni_transactions(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2021/SC/PSB/SA-PAID');
        $payment = $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);

        $other = $this->createAlumni($alumni->category, [
            'matric_number' => '2021/SC/PSB/SA-OTHER',
            'year_of_graduation' => 2026,
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);
        $otherPayment = $this->payFee($other, $registration);

        Livewire::actingAs($this->studentAffairsUser())
            ->test(StudentAffairsClearance::class)
            ->call('viewPaymentDetails', $alumni->id)
            ->assertSee($payment->payment_reference)
            ->assertSee('Paid')
            ->assertDontSee($otherPayment->payment_reference);
    }

    public function test_student_affairs_payment_modal_shows_outstanding_fees(): void
    {
        [$alumni, $registration] = $this->createUnpaidUgAlumni('2021/SC/PSB/SA-UNPAID');

        Livewire::actingAs($this->studentAffairsUser())
            ->test(StudentAffairsClearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSee('View Details')
            ->call('viewPaymentDetails', $alumni->id)
            ->assertSee($registration->description)
            ->assertSee('7,700.00', false)
            ->assertSee('Unpaid');
    }

    public function test_alumni_cannot_open_clearance_payment_details(): void
    {
        [$alumni] = $this->createUnpaidUgAlumni('2021/SC/PSB/MODAL-FORBIDDEN');
        $user = User::factory()->create();
        $user->assignRole('alumni');

        Livewire::actingAs($user)
            ->test(Clearance::class)
            ->call('viewPaymentDetails', $alumni->id)
            ->assertForbidden();

        Livewire::actingAs($user)
            ->test(StudentAffairsClearance::class)
            ->call('viewPaymentDetails', $alumni->id)
            ->assertForbidden();
    }

    public function test_incomplete_biodata_is_not_shown_as_done(): void
    {
        $category = AlumniCategory::firstOrCreate(
            ['slug' => 'undergraduate-full-time'],
            ['name' => 'Undergraduate Full Time', 'description' => 'Undergraduate full time alumni', 'is_active' => true]
        );

        $alumni = $this->createAlumni($category, [
            'matric_number' => '2021/SC/PSB/TEST-BIO',
            'contact_address' => null,
            'phone_number' => null,
            'qualification_type' => null,
        ]);

        $this->assertFalse($alumni->biodata_completed);
    }

    private function academicAffairsUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('academic-affairs');

        return $user;
    }

    private function studentAffairsUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('student-affairs');

        return $user;
    }

    /**
     * @return array{0: Alumni, 1: FeeTemplate, 2: FeeTemplate}
     */
    private function createUnpaidUgAlumni(string $matric): array
    {
        $category = AlumniCategory::firstOrCreate(
            ['slug' => 'undergraduate-full-time'],
            ['name' => 'Undergraduate Full Time', 'description' => 'Undergraduate full time alumni', 'is_active' => true]
        );

        $alumni = $this->createAlumni($category, [
            'matric_number' => $matric,
            'year_of_graduation' => 2026,
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);

        [$registrationType, $levyType] = $this->onboardingTypes();
        $registration = $this->createFee($registrationType, $category->id, 5000, 'Registration UG FT', 2026);
        $levy = $this->createFee($levyType, $category->id, 7700, 'Development levy UG FT', 2026);

        return [$alumni, $registration, $levy];
    }

    private function createAlumni(AlumniCategory $category, array $overrides = []): Alumni
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        return Alumni::create(array_merge([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'matric_number' => 'MAT'.uniqid(),
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'faculty' => 'Science',
            'year_of_graduation' => 2026,
            'date_of_birth' => '2000-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2021,
            'gender' => 'male',
        ], $overrides));
    }

    /**
     * @return array{0: FeeType, 1: FeeType}
     */
    private function onboardingTypes(): array
    {
        $registration = FeeType::firstOrCreate(
            ['code' => 'registration'],
            ['name' => 'Registration', 'description' => 'Registration', 'is_active' => true, 'is_system' => true]
        );
        $levy = FeeType::firstOrCreate(
            ['code' => 'development_levy'],
            ['name' => 'Development Levy', 'description' => 'Levy', 'is_active' => true, 'is_system' => true]
        );

        return [$registration, $levy];
    }

    private function createFee(FeeType $type, int $categoryId, float $amount, string $description, int $year): FeeTemplate
    {
        return FeeTemplate::create([
            'fee_type_id' => $type->id,
            'category_id' => $categoryId,
            'fee_purpose' => FeeTemplate::PURPOSE_ONBOARDING,
            'graduation_year' => $year,
            'amount' => $amount,
            'description' => $description,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => null,
        ]);
    }

    private function enableAcademicAffairsClearance(): void
    {
        OnboardingSetting::unguarded(function () {
            OnboardingSetting::updateOrCreate(
                ['id' => 1],
                ['is_academic_affairs_clearance_enabled' => true]
            );
        });
    }

    private function associationFee(Alumni $alumni, $validFrom): FeeTemplate
    {
        $type = FeeType::firstOrCreate(
            ['code' => 'alumni-association-registration'],
            ['name' => 'Alumni Association Registration', 'description' => 'Alumni Association Registration', 'is_active' => true, 'is_system' => false]
        );

        return FeeTemplate::create([
            'fee_type_id' => $type->id,
            'category_id' => $alumni->category_id,
            'fee_purpose' => null,
            'graduation_year' => $alumni->year_of_graduation,
            'amount' => 5000,
            'description' => null,
            'is_active' => true,
            'valid_from' => $validFrom,
            'valid_until' => null,
        ]);
    }

    private function attachCombinedFee(Alumni $alumni, FeeTemplate $fee, $attachedAt): void
    {
        $structure = PaymentStructure::create([
            'name' => 'Combined '.$alumni->matric_number,
            'payment_mode' => PaymentStructure::MODE_COMBINED,
            'graduation_year' => $alumni->year_of_graduation,
            'category_id' => $alumni->category_id,
            'is_active' => true,
        ]);
        $item = PaymentStructureItem::create([
            'payment_structure_id' => $structure->id,
            'fee_template_id' => $fee->id,
        ]);
        $item->forceFill([
            'created_at' => $attachedAt,
            'updated_at' => $attachedAt,
        ])->save();
    }

    private function payFee(Alumni $alumni, FeeTemplate $fee): Transaction
    {
        return Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $fee->id,
            'amount' => $fee->amount,
            'status' => 'paid',
            'payment_reference' => 'ALUMNI-'.uniqid(),
            'payment_provider' => 'credocentral',
            'paid_at' => now(),
        ]);
    }
}
