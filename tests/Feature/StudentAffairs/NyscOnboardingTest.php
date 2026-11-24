<?php

namespace Tests\Feature\StudentAffairs;

use App\Livewire\StudentAffairs\Clearance;
use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\ClearanceLog;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\OnboardingSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NyscOnboardingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('student-affairs');
        Role::findOrCreate('alumni');
        Permission::findOrCreate('toggle student affairs clearance');
        FeeType::where('code', 'subscription')->update(['is_active' => false]);

        OnboardingSetting::unguarded(function () {
            OnboardingSetting::updateOrCreate(
                ['id' => 1],
                ['is_student_affairs_clearance_enabled' => true]
            );
        });
    }

    public function test_onboard_stays_disabled_and_is_rejected_while_academic_affairs_is_pending(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2036/SC/PSB/NYSC-AA');
        $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);
        $officer = $this->officer();

        $component = Livewire::actingAs($officer)
            ->test(Clearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSee('AA Status')
            ->assertSee('NYSC Onboarding Status')
            ->assertSee('Actor')
            ->assertSee('Paid')
            ->assertSeeHtml('adt-status adt-status-inactive')
            ->assertDontSeeHtml('adt-status adt-status-active');

        $this->assertMatchesRegularExpression(
            '/wire:target="onboard\('.$alumni->id.'\)"\s+disabled/',
            $component->html()
        );

        $component->call('onboard', $alumni->id)
            ->assertSet('messageType', 'error');

        $this->assertFalse((bool) $alumni->fresh()->student_affairs_cleared);
        $this->assertSame(0, ClearanceLog::where('alumni_id', $alumni->id)->count());
    }

    public function test_unpaid_alumni_cannot_be_onboarded_even_after_academic_affairs_clearance(): void
    {
        [$alumni] = $this->createUnpaidUgAlumni('2036/SC/PSB/NYSC-UNPAID');
        $alumni->update(['academic_affairs_cleared' => true]);

        Livewire::actingAs($this->officer())
            ->test(Clearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSee('Unpaid')
            ->assertSee('Review')
            ->call('onboard', $alumni->id)
            ->assertSet('messageType', 'error');

        $this->assertFalse((bool) $alumni->fresh()->student_affairs_cleared);
    }

    public function test_paid_and_cleared_alumni_is_onboarded_once_by_the_signed_in_officer(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2036/SC/PSB/NYSC-OK');
        $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);
        $alumni->update(['academic_affairs_cleared' => true]);
        $officer = $this->officer();

        $component = Livewire::actingAs($officer)
            ->test(Clearance::class)
            ->set('search', $alumni->matric_number)
            ->assertSeeHtml('adt-status adt-status-active')
            ->assertDontSeeHtml('adt-status adt-status-inactive');

        $this->assertDoesNotMatchRegularExpression(
            '/wire:target="onboard\('.$alumni->id.'\)"\s+disabled/',
            $component->html()
        );

        $component->call('onboard', $alumni->id)
            ->assertSet('messageType', 'success')
            ->assertSee($officer->name)
            ->assertDontSeeHtml('onboard('.$alumni->id.')');

        $alumni->refresh();
        $this->assertTrue((bool) $alumni->student_affairs_cleared);

        $log = ClearanceLog::where('alumni_id', $alumni->id)->where('new_value', true)->first();
        $this->assertNotNull($log);
        $this->assertSame($officer->id, $log->actor_user_id);
        $this->assertSame('NYSC onboarding', $log->reason);
        $this->assertNotNull($log->created_at);

        $component->call('onboard', $alumni->id)
            ->assertSet('message', 'This alumnus has already been onboarded.');

        $this->assertSame(
            1,
            ClearanceLog::where('alumni_id', $alumni->id)->where('reason', 'NYSC onboarding')->count()
        );
    }

    public function test_other_roles_cannot_onboard_an_alumnus(): void
    {
        [$alumni] = $this->createUnpaidUgAlumni('2036/SC/PSB/NYSC-DENY');
        $user = User::factory()->create();
        $user->assignRole('alumni');

        Livewire::actingAs($user)
            ->test(Clearance::class)
            ->call('onboard', $alumni->id)
            ->assertForbidden();
    }

    public function test_legacy_toggle_rejects_onboarding_without_academic_affairs_clearance(): void
    {
        [$alumni, $registration, $levy] = $this->createUnpaidUgAlumni('2036/SC/PSB/NYSC-POST');
        $this->payFee($alumni, $registration);
        $this->payFee($alumni, $levy);

        $this->actingAs($this->officer())
            ->post(route('student-affairs.clearance.toggle', $alumni), ['value' => '1'])
            ->assertRedirect(route('student-affairs.clearance'));

        $this->assertFalse((bool) $alumni->fresh()->student_affairs_cleared);
        $this->assertSame(0, ClearanceLog::where('alumni_id', $alumni->id)->count());
    }

    private function officer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('student-affairs');
        $user->givePermissionTo('toggle student affairs clearance');

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

        $alumni = Alumni::create([
            'user_id' => User::factory()->create()->id,
            'category_id' => $category->id,
            'matric_number' => $matric,
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'faculty' => 'Science',
            'year_of_graduation' => 2036,
            'date_of_birth' => '2000-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2021,
            'gender' => 'male',
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);

        $registration = FeeType::firstOrCreate(
            ['code' => 'registration'],
            ['name' => 'Registration', 'description' => 'Registration', 'is_active' => true, 'is_system' => true]
        );
        $levy = FeeType::firstOrCreate(
            ['code' => 'development_levy'],
            ['name' => 'Development Levy', 'description' => 'Levy', 'is_active' => true, 'is_system' => true]
        );

        return [
            $alumni,
            $this->createFee($registration, $category->id, 5000, 'Registration '.$matric, 2036),
            $this->createFee($levy, $category->id, 7700, 'Levy '.$matric, 2036),
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
