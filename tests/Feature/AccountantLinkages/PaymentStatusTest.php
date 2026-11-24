<?php

namespace Tests\Feature\AccountantLinkages;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('accountant-linkages');
        Role::findOrCreate('alumni');
        FeeType::where('code', 'subscription')->update(['is_active' => false]);
    }

    public function test_payment_status_lists_2026_alumni_and_hides_earlier_years(): void
    {
        [$pending] = $this->createUnpaidUgAlumni('2035/SC/PSB/ACCT-PENDING', 2035);
        [$paid, $registration, $levy] = $this->createUnpaidUgAlumni('2036/SC/PSB/ACCT-PAID', 2036);
        $this->payFee($paid, $registration);
        $this->payFee($paid, $levy);
        $earlier = $this->createAlumni($this->ugCategory(), [
            'matric_number' => '2025/SC/PSB/ACCT-EARLY',
            'year_of_graduation' => 2025,
        ]);

        $officer = $this->officer();

        $this->actingAs($officer)
            ->get(route('accountant-linkages.payments', ['year' => 2025, 'search' => $pending->matric_number]))
            ->assertOk()
            ->assertSee('Payment status')
            ->assertSee($pending->matric_number)
            ->assertSee('Pending')
            ->assertDontSee($earlier->matric_number);

        $this->actingAs($officer)
            ->get(route('accountant-linkages.payments', ['search' => $paid->matric_number]))
            ->assertOk()
            ->assertSee($paid->matric_number)
            ->assertSee('Paid')
            ->assertDontSee($earlier->matric_number);

        $this->actingAs($officer)
            ->get(route('dashboard'))
            ->assertRedirect(route('accountant-linkages.payments'));
    }

    public function test_payment_status_dropdown_filters_paid_and_pending(): void
    {
        [$pending] = $this->createUnpaidUgAlumni('2035/SC/PSB/ACCT-FILTER-PENDING', 2035);
        [$paid, $registration, $levy] = $this->createUnpaidUgAlumni('2036/SC/PSB/ACCT-FILTER-PAID', 2036);
        $this->payFee($paid, $registration);
        $this->payFee($paid, $levy);

        $officer = $this->officer();

        $this->actingAs($officer)
            ->get(route('accountant-linkages.payments', ['status' => 'pending', 'search' => 'ACCT-FILTER']))
            ->assertOk()
            ->assertSee($pending->matric_number)
            ->assertDontSee($paid->matric_number);

        $this->actingAs($officer)
            ->get(route('accountant-linkages.payments', ['status' => 'paid', 'search' => 'ACCT-FILTER']))
            ->assertOk()
            ->assertSee($paid->matric_number)
            ->assertDontSee($pending->matric_number);
    }

    public function test_view_and_print_show_only_the_selected_alumni_2026_payments(): void
    {
        [$alumni, $registration] = $this->createUnpaidUgAlumni('2026/SC/PSB/ACCT-VIEW', 2037);
        $payment = $this->payFee($alumni, $registration);
        $alumni->user->forceFill(['avatar' => 'avatars/accountant-linkages.jpg'])->save();

        $other = $this->createAlumni($this->ugCategory(), [
            'matric_number' => '2026/SC/PSB/ACCT-OTHER',
            'year_of_graduation' => 2037,
        ]);
        $otherPayment = $this->payFee($other, $registration);

        $oldFee = $this->createFee(
            FeeType::firstOrCreate(['code' => 'registration'], ['name' => 'Registration', 'description' => 'Registration', 'is_active' => true, 'is_system' => true]),
            $alumni->category_id,
            1500,
            'Old registration',
            2024
        );
        $oldPayment = $this->payFee($alumni, $oldFee);

        $officer = $this->officer();

        $this->actingAs($officer)
            ->get(route('accountant-linkages.payments.show', $alumni))
            ->assertOk()
            ->assertSee('avatars/accountant-linkages.jpg')
            ->assertSee($alumni->matric_number)
            ->assertSee($registration->description)
            ->assertSee($payment->payment_reference)
            ->assertDontSee($otherPayment->payment_reference)
            ->assertDontSee($oldPayment->payment_reference);

        $this->actingAs($officer)
            ->get(route('accountant-linkages.payments.print', $alumni))
            ->assertOk()
            ->assertSee('accountant-print', false)
            ->assertSee($registration->description)
            ->assertSee($payment->payment_reference)
            ->assertSee('Print')
            ->assertDontSee($oldPayment->payment_reference);
    }

    public function test_view_lists_each_paid_item_with_its_own_amount(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2037/SC/PSB/ACCT-ITEMS', 2037);
        $transaction = Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => null,
            'amount' => $registration->amount + $levy->amount,
            'status' => 'paid',
            'payment_reference' => 'ALUMNI-COMBINED-ITEMS',
            'payment_provider' => 'credocentral',
            'paid_at' => now(),
        ]);

        foreach ([$registration, $levy] as $fee) {
            $transaction->items()->create([
                'fee_template_id' => $fee->id,
                'fee_type_id' => $fee->fee_type_id,
                'category_id' => $fee->category_id,
                'description' => $fee->description,
                'amount' => $fee->amount,
            ]);
        }

        $this->actingAs($this->officer())
            ->get(route('accountant-linkages.payments.show', $alumni))
            ->assertOk()
            ->assertSee($registration->description)
            ->assertSee('5,000.00')
            ->assertSee($levy->description)
            ->assertSee('7,700.00')
            ->assertDontSee('12,700.00');

        $this->actingAs($this->officer())
            ->get(route('accountant-linkages.payments.print', $alumni))
            ->assertOk()
            ->assertSee($registration->description)
            ->assertSee('5,000.00')
            ->assertSee($levy->description)
            ->assertSee('7,700.00');
    }

    public function test_alumni_cannot_open_accountant_linkages_payments(): void
    {
        [$alumni] = $this->createUnpaidUgAlumni('2026/SC/PSB/ACCT-DENY', 2038);
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $this->actingAs($user)
            ->get(route('accountant-linkages.payments'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('accountant-linkages.payments.show', $alumni))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('accountant-linkages.payments.print', $alumni))
            ->assertForbidden();
    }

    public function test_pre_2026_alumni_payment_record_cannot_be_opened_by_id(): void
    {
        $alumni = $this->createAlumni($this->ugCategory(), [
            'matric_number' => '2024/SC/PSB/ACCT-HIDDEN',
            'year_of_graduation' => 2024,
        ]);

        $this->actingAs($this->officer())
            ->get(route('accountant-linkages.payments.show', $alumni))
            ->assertNotFound();
    }

    private function officer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('accountant-linkages');

        return $user;
    }

    /**
     * @return array{0: Alumni, 1: FeeTemplate, 2: FeeTemplate}
     */
    private function createUnpaidUgAlumni(string $matric, int $year): array
    {
        $category = $this->ugCategory();
        $alumni = $this->createAlumni($category, [
            'matric_number' => $matric,
            'year_of_graduation' => $year,
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);

        [$registrationType, $levyType] = $this->onboardingTypes();

        return [
            $alumni,
            $this->createFee($registrationType, $category->id, 5000, 'Registration UG '.$year, $year),
            $this->createFee($levyType, $category->id, 7700, 'Development levy UG '.$year, $year),
        ];
    }

    private function ugCategory(): AlumniCategory
    {
        return AlumniCategory::firstOrCreate(
            ['slug' => 'undergraduate-full-time'],
            ['name' => 'Undergraduate Full Time', 'description' => 'Undergraduate full time alumni', 'is_active' => true]
        );
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
        return [
            FeeType::firstOrCreate(
                ['code' => 'registration'],
                ['name' => 'Registration', 'description' => 'Registration', 'is_active' => true, 'is_system' => true]
            ),
            FeeType::firstOrCreate(
                ['code' => 'development_levy'],
                ['name' => 'Development Levy', 'description' => 'Levy', 'is_active' => true, 'is_system' => true]
            ),
        ];
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
            'valid_from' => $year >= 2026 ? now()->subDay() : now()->subYears(3),
            'valid_until' => null,
        ]);
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
