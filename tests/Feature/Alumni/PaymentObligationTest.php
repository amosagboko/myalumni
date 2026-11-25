<?php

namespace Tests\Feature\Alumni;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\PaymentAttempt;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentCompletionService;
use App\Services\PaymentObligationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentObligationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
        FeeType::where('code', 'subscription')->update(['is_active' => false]);
    }

    public function test_a_failed_checkout_is_retried_on_the_same_obligation(): void
    {
        [$alumni, $fee] = $this->payableFee();
        $service = app(PaymentObligationService::class);

        $first = $service->claimFee($alumni, $fee, $this->claimAttributes($fee));
        $first->update([
            'status' => 'failed',
            'payment_link' => 'https://pay.example/first',
            'payment_provider_reference' => 'CREDO-FIRST',
        ]);
        $oldReference = $first->payment_reference;

        $second = $service->claimFee($alumni, $fee, $this->claimAttributes($fee));

        $this->assertSame($first->id, $second->id);
        $this->assertSame('pending', $second->status);
        $this->assertNotSame($oldReference, $second->payment_reference);
        $this->assertSame(1, Transaction::query()->where('alumni_id', $alumni->id)->where('fee_template_id', $fee->id)->obligations()->count());
        $this->assertDatabaseHas('payment_attempts', [
            'transaction_id' => $first->id,
            'payment_reference' => $oldReference,
            'status' => 'failed',
        ]);

        $this->actingAs($alumni->user)
            ->get(route('alumni.payments.history'))
            ->assertOk()
            ->assertSee($second->payment_reference)
            ->assertDontSee($oldReference);
    }

    public function test_history_hides_a_linked_attempt_and_a_second_success_does_not_add_a_row(): void
    {
        [$alumni, $fee] = $this->payableFee();
        $service = app(PaymentObligationService::class);
        $obligation = $service->claimFee($alumni, $fee, $this->claimAttributes($fee));

        $linked = Transaction::create([
            'alumni_id' => $alumni->id,
            'obligation_id' => $obligation->id,
            'fee_template_id' => $fee->id,
            'amount' => $fee->amount,
            'status' => 'failed',
            'payment_reference' => 'ALUMNI-LINKED-'.uniqid(),
            'payment_provider' => 'credocentral',
        ]);

        $this->actingAs($alumni->user)
            ->get(route('alumni.payments.history'))
            ->assertOk()
            ->assertSee($obligation->payment_reference)
            ->assertDontSee($linked->payment_reference);

        $completion = app(PaymentCompletionService::class);
        $completion->complete($obligation, now()->toIso8601String());
        $completion->complete($obligation->fresh(), now()->toIso8601String());

        $this->assertSame(1, Transaction::query()->where('alumni_id', $alumni->id)->where('fee_template_id', $fee->id)->where('status', 'paid')->count());
        $this->assertTrue($obligation->fresh()->isPaid());
    }

    public function test_pay_now_after_failure_does_not_create_another_history_row(): void
    {
        [$alumni, $fee] = $this->payableFee();
        $service = app(PaymentObligationService::class);
        $obligation = $service->claimFee($alumni, $fee, $this->claimAttributes($fee));
        $obligation->update(['status' => 'failed']);

        config([
            'services.credocentral.public_key' => 'pk_test',
            'services.credocentral.secret_key' => 'sk_test',
            'services.credocentral.service_codes.registration' => [
                $fee->category->slug => '003486PYW8VS',
            ],
        ]);

        Http::fake([
            '*' => Http::response([
                'data' => [
                    'authorizationUrl' => 'https://pay.example/retry',
                    'credoReference' => 'CREDO-RETRY',
                ],
            ], 200),
        ]);

        $this->actingAs($alumni->user)
            ->post(route('alumni.payments.initiate'), ['fee_id' => $fee->id])
            ->assertRedirect('https://pay.example/retry');

        $this->assertSame(1, Transaction::query()->where('alumni_id', $alumni->id)->where('fee_template_id', $fee->id)->obligations()->count());
        $this->assertSame('pending', $obligation->fresh()->status);
        $this->assertTrue(PaymentAttempt::query()->where('transaction_id', $obligation->id)->where('status', 'pending')->exists());
    }

    /**
     * @return array{0: Alumni, 1: FeeTemplate}
     */
    private function payableFee(): array
    {
        $category = AlumniCategory::create([
            'slug' => 'obligation-'.uniqid(),
            'name' => 'Obligation Test',
            'description' => 'Obligation test category',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $user->assignRole('alumni');
        $alumni = Alumni::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'matric_number' => 'MAT'.uniqid(),
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'faculty' => 'Science',
            'year_of_graduation' => 2035,
            'date_of_birth' => '2000-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2021,
            'gender' => 'female',
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);
        $type = FeeType::firstOrCreate(
            ['code' => 'registration'],
            ['name' => 'Registration', 'description' => 'Registration', 'is_active' => true, 'is_system' => true]
        );
        $fee = FeeTemplate::create([
            'fee_type_id' => $type->id,
            'category_id' => $category->id,
            'fee_purpose' => FeeTemplate::PURPOSE_ONBOARDING,
            'graduation_year' => 2035,
            'amount' => 25000,
            'description' => 'Registration obligation '.uniqid(),
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => null,
        ]);

        return [$alumni, $fee];
    }

    /**
     * @return array<string, mixed>
     */
    private function claimAttributes(FeeTemplate $fee): array
    {
        return [
            'amount' => $fee->amount,
            'payment_reference' => 'ALUMNI-'.strtoupper(uniqid()),
            'payment_provider' => 'credocentral',
            'payment_details' => [
                'fee_description' => $fee->description,
            ],
        ];
    }
}
