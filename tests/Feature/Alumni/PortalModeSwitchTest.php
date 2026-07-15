<?php

namespace Tests\Feature\Alumni;

use App\Models\Alumni;
use App\Models\User;
use App\Services\PortalModeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortalModeSwitchTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['alumni', 'elcom-chairman', 'alumni-agent', 'alumni-president'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_alumni_president_defaults_to_president_office_home(): void
    {
        $user = $this->alumniPresidentUser();

        $this->assertSame('alumni-president.dashboard', app(PortalModeService::class)->resolveHomeRoute($user));
    }

    public function test_alumni_president_can_switch_to_member_portal(): void
    {
        $user = $this->alumniPresidentUser();

        $response = $this->actingAs($user)->post(route('portal.switch'), [
            'mode' => PortalModeService::MODE_MEMBER,
        ]);

        $response->assertRedirect(route('alumni.home'));
        $this->assertTrue(app(PortalModeService::class)->hasDualPortalAccess($user));
    }

    public function test_alumni_president_login_redirects_to_president_dashboard(): void
    {
        $user = $this->alumniPresidentUser();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('alumni-president.dashboard'));
    }

    private function alumniPresidentUser(bool $completeBioData = true): User
    {
        $user = User::factory()->create();
        $user->assignRole('alumni-president');

        Alumni::create($this->alumniAttributes($user, $completeBioData));

        return $user;
    }

    public function test_dual_role_user_defaults_to_operational_home(): void
    {
        $user = $this->dualRoleUser();

        $this->assertSame('elcom-chairman.dashboard', app(PortalModeService::class)->resolveHomeRoute($user));
    }

    public function test_dual_role_user_can_switch_to_member_portal(): void
    {
        $user = $this->dualRoleUser();

        $response = $this->actingAs($user)->post(route('portal.switch'), [
            'mode' => PortalModeService::MODE_MEMBER,
        ]);

        $response->assertRedirect(route('alumni.home'));
        $this->assertSame(PortalModeService::MODE_MEMBER, session(PortalModeService::SESSION_KEY));
    }

    public function test_dual_role_user_can_switch_back_to_operations(): void
    {
        $user = $this->dualRoleUser();

        $this->actingAs($user)->post(route('portal.switch'), [
            'mode' => PortalModeService::MODE_MEMBER,
        ]);

        $response = $this->actingAs($user)->post(route('portal.switch'), [
            'mode' => PortalModeService::MODE_OPERATIONAL,
        ]);

        $response->assertRedirect(route('elcom-chairman.dashboard'));
        $this->assertSame(PortalModeService::MODE_OPERATIONAL, session(PortalModeService::SESSION_KEY));
    }

    public function test_member_mode_enforces_portal_gate_for_limited_dual_user(): void
    {
        $user = $this->dualRoleUser(completeBioData: false);

        $this->actingAs($user)->post(route('portal.switch'), [
            'mode' => PortalModeService::MODE_MEMBER,
        ]);

        $response = $this->actingAs($user)->get(route('alumni.home'));

        $response->assertRedirect(route('alumni.bio-data'));
    }

    public function test_visiting_alumni_home_syncs_member_mode_and_enforces_gate(): void
    {
        $user = $this->dualRoleUser(completeBioData: false);

        session([PortalModeService::SESSION_KEY => PortalModeService::MODE_OPERATIONAL]);

        $response = $this->actingAs($user)->get(route('alumni.home'));

        $response->assertRedirect(route('alumni.bio-data'));
        $this->assertSame(PortalModeService::MODE_MEMBER, session(PortalModeService::SESSION_KEY));
    }

    public function test_operational_dashboard_skips_member_gate_in_operational_mode(): void
    {
        $user = $this->dualRoleUser(completeBioData: false);

        session([PortalModeService::SESSION_KEY => PortalModeService::MODE_OPERATIONAL]);

        $response = $this->actingAs($user)->get(route('elcom-chairman.dashboard'));

        $response->assertOk();
    }

    public function test_single_role_alumni_cannot_switch_portal(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        Alumni::create($this->alumniAttributes($user));

        $response = $this->actingAs($user)->post(route('portal.switch'), [
            'mode' => PortalModeService::MODE_OPERATIONAL,
        ]);

        $response->assertForbidden();
    }

    public function test_visiting_member_route_syncs_session_to_member_mode(): void
    {
        $user = $this->dualRoleUser();

        session([PortalModeService::SESSION_KEY => PortalModeService::MODE_OPERATIONAL]);

        $this->actingAs($user)->get(route('alumni.discover'));

        $this->assertSame(PortalModeService::MODE_MEMBER, session(PortalModeService::SESSION_KEY));
    }

    public function test_visiting_operational_route_syncs_session_to_operational_mode(): void
    {
        $user = $this->dualRoleUser();

        $this->actingAs($user)->post(route('portal.switch'), [
            'mode' => PortalModeService::MODE_MEMBER,
        ]);

        $this->actingAs($user)->get(route('elcom-chairman.dashboard'));

        $this->assertSame(PortalModeService::MODE_OPERATIONAL, session(PortalModeService::SESSION_KEY));
    }

    public function test_login_redirect_uses_operational_home_for_dual_role_user(): void
    {
        $user = $this->dualRoleUser();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('elcom-chairman.dashboard'));
    }

    private function dualRoleUser(bool $completeBioData = true): User
    {
        $user = User::factory()->create();
        $user->assignRole('elcom-chairman');
        $user->assignRole('alumni');

        Alumni::create($this->alumniAttributes($user, $completeBioData));

        return $user;
    }

    private function alumniAttributes(User $user, bool $completeBioData = true): array
    {
        return [
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
        ];
    }
}
