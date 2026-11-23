<?php

namespace Tests\Feature\Admin;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Alumni\AlumniMemberAccessService;
use App\Services\Alumni\SkippedPaymentReportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SkippedPaymentReportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('administrator');
        Role::findOrCreate('alumni');
        FeeType::where('code', 'subscription')->update(['is_active' => false]);
    }

    public function test_unpaid_online_onboarder_appears_on_the_skipped_payments_list(): void
    {
        [$unpaid, $registration, $levy] = $this->createUnpaidUgAlumni('2021/SC/PSB/SKIP-1', createdBy: null);
        $paid = $this->createAlumni($this->ugCategory(), [
            'matric_number' => '2021/SC/PSB/SKIP-PAID',
            'year_of_graduation' => 2033,
            'created_by' => null,
        ]);
        $this->payFee($paid, $registration);
        $this->payFee($paid, $levy);

        $rows = app(SkippedPaymentReportService::class)->rows(['year' => 2033]);
        $matrics = $rows->pluck('matric_number');

        $this->assertTrue($matrics->contains($unpaid->matric_number));
        $this->assertFalse($matrics->contains($paid->matric_number));
        $this->assertFalse((bool) $rows->firstWhere('matric_number', $unpaid->matric_number)['bypassed_payment_gate']);
    }

    public function test_alumni_with_no_active_fee_lines_are_flagged_as_bypassing_the_gate(): void
    {
        $alumni = $this->createUnpaidUgAlumniWithoutTemplates('2021/SC/PSB/SKIP-BYPASS');

        $this->assertFalse($alumni->hasCompletedRequiredPayments());
        $this->assertTrue($alumni->getActiveFees()->isEmpty());

        $rows = app(SkippedPaymentReportService::class)->rows([
            'year' => 2034,
            'bypassed' => true,
        ]);

        $this->assertTrue($rows->pluck('matric_number')->contains($alumni->matric_number));
        $this->assertTrue((bool) $rows->firstWhere('matric_number', $alumni->matric_number)['bypassed_payment_gate']);
        $this->assertFalse(app(AlumniMemberAccessService::class)->isFullMember($alumni));
    }

    public function test_admin_can_view_and_export_the_skipped_payments_report(): void
    {
        [$alumni] = $this->createUnpaidUgAlumni('2021/SC/PSB/SKIP-UI', createdBy: null);
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.skipped-payments.index', ['year' => 2033, 'search' => $alumni->matric_number]))
            ->assertOk()
            ->assertSee($alumni->matric_number)
            ->assertSee('Skipped payments')
            ->assertSee('Download CSV');

        $csv = $this->actingAs($admin)
            ->get(route('admin.skipped-payments.export', ['year' => 2033, 'search' => $alumni->matric_number]));

        $csv->assertOk();
        $csv->assertHeader('content-disposition');
        $this->assertStringContainsString($alumni->matric_number, $csv->streamedContent());
        $this->assertStringContainsString('Matriculation Number', $csv->streamedContent());
    }

    public function test_artisan_command_lists_unpaid_alumni(): void
    {
        [$alumni] = $this->createUnpaidUgAlumni('2021/SC/PSB/SKIP-CLI', createdBy: null);

        $this->artisan('alumni:skipped-payments', ['--year' => 2033])
            ->expectsOutputToContain($alumni->matric_number)
            ->assertSuccessful();
    }

    public function test_non_admin_cannot_open_skipped_payments(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $this->actingAs($user)
            ->get(route('admin.skipped-payments.index'))
            ->assertForbidden();
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        return $user;
    }

    /**
     * @return array{0: Alumni, 1: FeeTemplate, 2: FeeTemplate}
     */
    private function createUnpaidUgAlumni(string $matric, ?int $createdBy = null): array
    {
        $category = $this->ugCategory();
        $alumni = $this->createAlumni($category, [
            'matric_number' => $matric,
            'year_of_graduation' => 2033,
            'created_by' => $createdBy,
        ]);

        [$registrationType, $levyType] = $this->onboardingTypes();
        $registration = $this->createFee($registrationType, $category->id, 5000, 'Registration UG FT', 2033);
        $levy = $this->createFee($levyType, $category->id, 7700, 'Development levy UG FT', 2033);

        return [$alumni, $registration, $levy];
    }

    private function createUnpaidUgAlumniWithoutTemplates(string $matric): Alumni
    {
        $category = AlumniCategory::create([
            'slug' => 'skipped-payments-'.uniqid(),
            'name' => 'Skipped Payments Test',
            'description' => 'Isolated category for skipped payment tests',
            'is_active' => true,
        ]);

        return $this->createAlumni($category, [
            'matric_number' => $matric,
            'year_of_graduation' => 2034,
            'created_by' => null,
        ]);
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
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
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

    private function createFee(FeeType $type, int $categoryId, float $amount, string $description, int $year = 2033): FeeTemplate
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

    private function payFee(Alumni $alumni, FeeTemplate $fee): void
    {
        Transaction::create([
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
