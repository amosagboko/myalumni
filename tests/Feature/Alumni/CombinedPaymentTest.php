<?php

namespace Tests\Feature\Alumni;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\PaymentStructure;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\AlumniDuesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CombinedPaymentTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
        Role::findOrCreate('administrator');
    }

    public function test_ug_full_time_combined_structure_surfaces_one_checkout(): void
    {
        [$alumni, $registration, $levy] = $this->createUgFullTimeOnboardingPair();
        $this->createCombinedStructure([$registration, $levy], $alumni->category_id, $alumni->year_of_graduation);

        $service = app(AlumniDuesService::class);
        $checkout = $service->resolveCombinedCheckout($alumni);

        $this->assertNotNull($checkout);
        $this->assertCount(2, $checkout['fees']);
        $this->assertEquals(12700.0, $checkout['total']);
        $this->assertFalse($service->feeIsPayableByAlumni($registration, $alumni));
        $this->assertFalse($service->feeIsPayableByAlumni($levy, $alumni));
        $this->assertTrue($service->getActiveFees($alumni)->pluck('id')->sort()->values()->all() === collect([$registration->id, $levy->id])->sort()->values()->all());
    }

    public function test_paid_combined_transaction_marks_each_item_paid_without_double_counting(): void
    {
        [$alumni, $registration, $levy] = $this->createUgFullTimeOnboardingPair();
        $structure = $this->createCombinedStructure([$registration, $levy], $alumni->category_id, $alumni->year_of_graduation);

        $transaction = Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => null,
            'payment_structure_id' => $structure->id,
            'amount' => 12700,
            'status' => 'paid',
            'payment_reference' => 'ALUMNI-COMBINED-'.uniqid(),
            'payment_provider' => 'credocentral',
            'paid_at' => now(),
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'fee_template_id' => $registration->id,
            'fee_type_id' => $registration->fee_type_id,
            'category_id' => $registration->category_id,
            'description' => $registration->description,
            'amount' => $registration->amount,
        ]);
        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'fee_template_id' => $levy->id,
            'fee_type_id' => $levy->fee_type_id,
            'category_id' => $levy->category_id,
            'description' => $levy->description,
            'amount' => $levy->amount,
        ]);

        $this->assertTrue($registration->isPaidByAlumni($alumni));
        $this->assertTrue($levy->isPaidByAlumni($alumni));
        $this->assertNull(app(AlumniDuesService::class)->resolveCombinedCheckout($alumni));
        $this->assertSame(12700.0, (float) Transaction::where('alumni_id', $alumni->id)->paid()->sum('amount'));
    }

    public function test_already_paid_item_is_excluded_and_one_leftover_falls_back_to_separate(): void
    {
        [$alumni, $registration, $levy] = $this->createUgFullTimeOnboardingPair();
        $this->createCombinedStructure([$registration, $levy], $alumni->category_id, $alumni->year_of_graduation);

        Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $registration->id,
            'amount' => $registration->amount,
            'status' => 'paid',
            'payment_reference' => 'ALUMNI-REG-'.uniqid(),
            'payment_provider' => 'credocentral',
            'paid_at' => now(),
        ]);

        $service = app(AlumniDuesService::class);
        $this->assertNull($service->resolveCombinedCheckout($alumni));
        $this->assertTrue($registration->isPaidByAlumni($alumni));
        $this->assertFalse($levy->isPaidByAlumni($alumni));
        $this->assertTrue($service->feeIsPayableByAlumni($levy, $alumni));
    }

    public function test_diploma_alumni_stay_on_separate_payment_without_combined_service_code(): void
    {
        $diploma = AlumniCategory::firstOrCreate(
            ['slug' => 'diploma'],
            ['name' => 'Diploma', 'description' => 'Diploma alumni', 'is_active' => true]
        );

        $alumni = $this->createAlumni($diploma, 2035);
        [$registrationType, $levyType] = $this->onboardingTypes();

        $registration = $this->createFee($registrationType, $diploma->id, 5000, 'Registration diploma', 2035);
        $levy = $this->createFee($levyType, $diploma->id, 5000, 'Levy diploma', 2035);
        $this->createCombinedStructure([$registration, $levy], $diploma->id, 2035);

        $service = app(AlumniDuesService::class);
        $this->assertNull($service->resolveCombinedCheckout($alumni));
        $this->assertTrue($service->feeIsPayableByAlumni($registration, $alumni));
        $this->assertTrue($service->feeIsPayableByAlumni($levy, $alumni));
    }

    public function test_separate_path_unchanged_without_a_structure(): void
    {
        [$alumni, $registration, $levy] = $this->createUgFullTimeOnboardingPair();
        $service = app(AlumniDuesService::class);

        $this->assertNull($service->resolveCombinedCheckout($alumni));
        $this->assertTrue($service->feeIsPayableByAlumni($registration, $alumni));
        $this->assertTrue($service->feeIsPayableByAlumni($levy, $alumni));
        $this->assertCount(2, $service->getActiveFees($alumni));
    }

    public function test_admin_can_create_combined_structure(): void
    {
        [$alumni, $registration, $levy] = $this->createUgFullTimeOnboardingPair();

        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $this->actingAs($admin)
            ->post(route('admin.payment-structures.store'), [
                'name' => 'UG FT 2025 pack',
                'display_title' => 'Undergraduate Full-Time Combined Fees',
                'payment_mode' => PaymentStructure::MODE_COMBINED,
                'graduation_year' => $alumni->year_of_graduation,
                'category_id' => $alumni->category_id,
                'is_active' => 1,
                'fee_template_ids' => [$registration->id, $levy->id],
            ])
            ->assertRedirect(route('admin.payment-structures.index'));

        $this->assertDatabaseHas('payment_structures', [
            'display_title' => 'Undergraduate Full-Time Combined Fees',
            'payment_mode' => PaymentStructure::MODE_COMBINED,
        ]);
    }

    public function test_credo_redirect_marks_combined_items_paid_once_and_shows_receipt(): void
    {
        [$alumni, $registration, $levy] = $this->createUgFullTimeOnboardingPair();
        $structure = $this->createCombinedStructure([$registration, $levy], $alumni->category_id, $alumni->year_of_graduation);
        $transaction = $this->createPendingCombinedTransaction($alumni, $structure, $registration, $levy);
        $transRef = 'CREDO-COMBINED-REF';

        $this->fakeSuccessfulCredoVerify($transaction, $transRef);

        $this->get(route('alumni.payments.redirect', [
            'reference' => $transaction->payment_reference,
            'transRef' => $transRef,
            'status' => 'success',
        ]))->assertRedirect(route('alumni.payments.success', $transaction));

        $transaction->refresh();
        $this->assertTrue($transaction->isPaid());
        $this->assertSame($transRef, $transaction->payment_provider_reference);
        $this->assertTrue($registration->isPaidByAlumni($alumni));
        $this->assertTrue($levy->isPaidByAlumni($alumni));
        $this->assertSame(1, Transaction::where('alumni_id', $alumni->id)->paid()->count());
        $this->assertSame(12700.0, (float) Transaction::where('alumni_id', $alumni->id)->paid()->sum('amount'));

        $this->get(route('alumni.payments.redirect', [
            'reference' => $transaction->payment_reference,
            'transRef' => $transRef,
            'status' => 'success',
        ]))->assertRedirect(route('alumni.payments.success', $transaction));

        $this->assertSame(1, Transaction::where('alumni_id', $alumni->id)->paid()->count());
        $this->assertSame(2, $transaction->items()->count());

        $this->actingAs($alumni->user)
            ->get(route('alumni.payments.success', $transaction))
            ->assertOk()
            ->assertSee('Combined payment successful')
            ->assertSee('Undergraduate Full-Time Combined Fees')
            ->assertSee('Registration UG FT')
            ->assertSee('Development levy UG FT')
            ->assertSee($transaction->payment_reference)
            ->assertSee($transRef)
            ->assertSee('12,700.00');
    }

    public function test_credo_redirect_for_separate_payment_still_marks_only_that_fee(): void
    {
        [$alumni, $registration, $levy] = $this->createUgFullTimeOnboardingPair();
        $transaction = Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $registration->id,
            'amount' => $registration->amount,
            'status' => 'pending',
            'payment_reference' => 'ALUMNI-SEP-'.uniqid(),
            'payment_provider' => 'credocentral',
        ]);
        $transRef = 'CREDO-SEPARATE-REF';

        $this->fakeSuccessfulCredoVerify($transaction, $transRef);

        $this->get(route('alumni.payments.redirect', [
            'reference' => $transaction->payment_reference,
            'transRef' => $transRef,
            'status' => 'success',
        ]))->assertRedirect(route('alumni.payments.success', $transaction));

        $this->assertTrue($registration->fresh()->isPaidByAlumni($alumni));
        $this->assertFalse($levy->fresh()->isPaidByAlumni($alumni));

        $this->actingAs($alumni->user)
            ->get(route('alumni.payments.success', $transaction))
            ->assertOk()
            ->assertSee('Payment Successful')
            ->assertSee('Registration UG FT')
            ->assertDontSee('Development levy UG FT');
    }

    public function test_unpaid_success_url_redirects_to_pending_and_is_owner_only(): void
    {
        [$alumni, $registration, $levy] = $this->createUgFullTimeOnboardingPair();
        $structure = $this->createCombinedStructure([$registration, $levy], $alumni->category_id, $alumni->year_of_graduation);
        $transaction = $this->createPendingCombinedTransaction($alumni, $structure, $registration, $levy);

        $this->actingAs($alumni->user)
            ->get(route('alumni.payments.success', $transaction))
            ->assertRedirect(route('alumni.payments.pending', $transaction));

        $this->actingAs($alumni->user)
            ->get(route('alumni.payments.pending', $transaction))
            ->assertOk()
            ->assertSee('Registration UG FT')
            ->assertSee('Development levy UG FT');

        $other = $this->createAlumni(
            AlumniCategory::firstOrCreate(
                ['slug' => 'undergraduate-full-time'],
                ['name' => 'Undergraduate Full Time', 'description' => 'Undergraduate full time alumni', 'is_active' => true]
            ),
            2035
        );

        $this->actingAs($other->user)
            ->get(route('alumni.payments.success', $transaction))
            ->assertForbidden();
    }

    /**
     * @return array{0: Alumni, 1: FeeTemplate, 2: FeeTemplate}
     */
    private function createUgFullTimeOnboardingPair(): array
    {
        $category = AlumniCategory::firstOrCreate(
            ['slug' => 'undergraduate-full-time'],
            ['name' => 'Undergraduate Full Time', 'description' => 'Undergraduate full time alumni', 'is_active' => true]
        );

        $alumni = $this->createAlumni($category, 2035);
        [$registrationType, $levyType] = $this->onboardingTypes();
        $registration = $this->createFee($registrationType, $category->id, 5000, 'Registration UG FT', 2035);
        $levy = $this->createFee($levyType, $category->id, 7700, 'Development levy UG FT', 2035);

        return [$alumni, $registration, $levy];
    }

    private function createAlumni(AlumniCategory $category, int $year): Alumni
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        return Alumni::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'matric_number' => 'MAT'.uniqid(),
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'faculty' => 'Science',
            'year_of_graduation' => $year,
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

    private function createFee(FeeType $type, int $categoryId, float $amount, string $description, int $year = 2035): FeeTemplate
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

    private function createCombinedStructure(array $templates, int $categoryId, int $year): PaymentStructure
    {
        $structure = PaymentStructure::create([
            'name' => 'UG FT combined '.$year,
            'display_title' => 'Undergraduate Full-Time Combined Fees',
            'payment_mode' => PaymentStructure::MODE_COMBINED,
            'graduation_year' => $year,
            'category_id' => $categoryId,
            'credo_service_code_key' => 'combined',
            'is_active' => true,
        ]);

        $structure->feeTemplates()->sync(collect($templates)->pluck('id')->all());

        return $structure;
    }

    private function createPendingCombinedTransaction(
        Alumni $alumni,
        PaymentStructure $structure,
        FeeTemplate $registration,
        FeeTemplate $levy
    ): Transaction {
        $transaction = Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => null,
            'payment_structure_id' => $structure->id,
            'amount' => 12700,
            'status' => 'pending',
            'payment_reference' => 'ALUMNI-COMBINED-'.uniqid(),
            'payment_provider' => 'credocentral',
        ]);

        foreach ([$registration, $levy] as $fee) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'fee_template_id' => $fee->id,
                'fee_type_id' => $fee->fee_type_id,
                'category_id' => $fee->category_id,
                'description' => $fee->description,
                'amount' => $fee->amount,
            ]);
        }

        return $transaction;
    }

    private function fakeSuccessfulCredoVerify(Transaction $transaction, string $transRef): void
    {
        $amount = (float) $transaction->amount;

        Http::fake(function (\Illuminate\Http\Client\Request $request) use ($transaction, $transRef, $amount) {
            if (! str_contains($request->url(), '/verify')) {
                return Http::response(['message' => 'Unexpected Credo URL: '.$request->url()], 500);
            }

            return Http::response([
                'data' => [
                    'status' => 'successful',
                    'transAmount' => (int) round($amount * 100),
                    'businessRef' => $transaction->payment_reference,
                    'transRef' => $transRef,
                    'transactionDate' => now()->toIso8601String(),
                ],
            ], 200);
        });
    }
}
