<?php

namespace Tests\Feature\Alumni;

use App\Models\Alumni;
use App\Models\User;
use App\Services\AlumniDuesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClearanceFormTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
    }

    private function allowClearanceAccess(): void
    {
        $this->mock(AlumniDuesService::class, function ($mock) {
            $mock->shouldReceive('getActiveFees')->andReturn(collect());
            $mock->shouldReceive('hasCompletedOnboardingFees')->andReturn(true);
            $mock->shouldReceive('getDuesPhase')->andReturn(AlumniDuesService::PHASE_NONE);
        });
    }

    private function createAlumni(User $user, array $overrides = []): Alumni
    {
        return Alumni::create(array_merge([
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
        ], $overrides));
    }

    public function test_clearance_form_shows_gate_when_profile_incomplete(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $this->createAlumni($user);

        $response = $this->actingAs($user->fresh(['alumni']))->get(route('reports'));

        $response->assertOk();
        $response->assertSee('Complete the steps below to unlock your clearance form.');
        $response->assertSee('Complete Bio-data');
        $response->assertDontSee('Print Form', false);
    }

    public function test_clearance_form_renders_for_completed_profile(): void
    {
        $this->allowClearanceAccess();

        $user = User::factory()->create(['name' => 'Jane Alumni']);
        $user->assignRole('alumni');

        $this->createAlumni($user, [
            'gender' => 'female',
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
            'qualification_details' => 'Computer Science',
        ]);

        $response = $this->actingAs($user->fresh(['alumni']))->get(route('reports'));

        $response->assertOk();
        $response->assertSee('Clearance Form');
        $response->assertSee('Jane Alumni');
        $response->assertSee(route('reports.print'), false);
        $response->assertSee(route('reports.download-pdf'), false);
    }

    public function test_print_route_requires_authentication_and_completed_profile(): void
    {
        $this->allowClearanceAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');

        $alumni = $this->createAlumni($user);

        $this->get(route('reports.print'))->assertRedirect(route('login'));

        $response = $this->actingAs($user->fresh(['alumni']))->get(route('reports.print'));
        $response->assertRedirect(route('reports'));

        Alumni::whereKey($alumni->id)->update([
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);

        $response = $this->actingAs($user->fresh(['alumni']))->get(route('reports.print'));
        $response->assertOk();
        $response->assertSee('Alumni Personal Data Registration Form');
        $response->assertSee('Print Form');
    }

    public function test_legacy_print_route_redirects_to_secure_print(): void
    {
        $this->allowClearanceAccess();

        $user = User::factory()->create();
        $user->assignRole('alumni');

        $alumni = $this->createAlumni($user, [
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);

        $this->actingAs($user->fresh(['alumni']))
            ->get(route('alumni.print', ['id' => $alumni->id]))
            ->assertRedirect(route('reports.print'));
    }

    public function test_clearance_form_legacy_route_redirects_to_reports(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $this->actingAs($user)
            ->get('/reports/clearance-form')
            ->assertRedirect('/reports');
    }
}
