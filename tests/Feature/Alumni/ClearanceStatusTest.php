<?php

namespace Tests\Feature\Alumni;

use App\Models\Alumni;
use App\Models\User;
use App\Services\AlumniDuesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClearanceStatusTest extends TestCase
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

    public function test_clearance_status_page_requires_authentication(): void
    {
        $this->get(route('alumni.clearance-status'))->assertRedirect(route('login'));
    }

    public function test_clearance_status_shows_warning_without_alumni_record(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $response = $this->actingAs($user)->get(route('alumni.clearance-status'));

        $response->assertOk();
        $response->assertSee('No alumni record found.');
        $response->assertSee('Complete Bio-data');
    }

    public function test_clearance_status_shows_not_required_for_pre_2025_graduates(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create(['name' => 'Older Graduate']);
        $user->assignRole('alumni');

        $this->createAlumni($user, [
            'year_of_graduation' => 2020,
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
        ]);

        $response = $this->actingAs($user->fresh(['alumni']))->get(route('alumni.clearance-status'));

        $response->assertOk();
        $response->assertSee('Division clearance is not required');
        $response->assertSee('Older Graduate');
        $response->assertDontSee('University division clearance');
    }

    public function test_clearance_status_shows_division_status_for_2025_graduates(): void
    {
        $this->allowPortalAccess();

        $user = User::factory()->create(['name' => 'Recent Graduate']);
        $user->assignRole('alumni');

        $this->createAlumni($user, [
            'year_of_graduation' => 2025,
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
            'student_affairs_cleared' => true,
            'academic_affairs_cleared' => false,
        ]);

        $response = $this->actingAs($user->fresh(['alumni']))->get(route('alumni.clearance-status'));

        $response->assertOk();
        $response->assertSee('University division clearance');
        $response->assertSee('Student Affairs Division');
        $response->assertSee('Academic Affairs Division');
        $response->assertSee('Division clearance pending');
        $response->assertSee(route('reports'), false);
    }

    public function test_clearance_status_shows_portal_pending_actions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $this->createAlumni($user, [
            'year_of_graduation' => 2025,
        ]);

        $response = $this->actingAs($user->fresh(['alumni']))->get(route('alumni.clearance-status'));

        $response->assertOk();
        $response->assertSee('Portal requirements pending');
        $response->assertSee('Complete Bio-data');
        $response->assertDontSee('Open Clearance Form');
    }
}
