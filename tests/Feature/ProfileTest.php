<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use DatabaseTransactions;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_name_email_and_delete_account_are_read_only_on_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Locked Name',
            'email' => 'locked@example.com',
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Locked Name')
            ->assertSee('locked@example.com')
            ->assertSee('Name and email cannot be edited from this page.')
            ->assertSee('Account deletion is disabled.')
            ->assertDontSee('name="name"', false)
            ->assertDontSee('name="email"', false)
            ->assertDontSee('_method" value="PATCH"', false)
            ->assertDontSee('_method" value="DELETE"', false);
    }

    public function test_profile_information_cannot_be_updated(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ])
            ->assertForbidden();

        $user->refresh();

        $this->assertSame('Original Name', $user->name);
        $this->assertSame('original@example.com', $user->email);
    }

    public function test_user_cannot_delete_their_account_from_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ])
            ->assertForbidden();

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh());
    }

    public function test_admin_profile_also_locks_name_email_and_deletion(): void
    {
        Role::findOrCreate('administrator');

        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin-locked@example.com',
        ]);
        $user->assignRole('administrator');

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Admin User')
            ->assertSee('admin-locked@example.com')
            ->assertSee('Name and email cannot be edited from this page.')
            ->assertSee('Account deletion is disabled.')
            ->assertDontSee('name="name"', false)
            ->assertDontSee('name="email"', false)
            ->assertDontSee('_method" value="PATCH"', false)
            ->assertDontSee('_method" value="DELETE"', false);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Hacked Admin',
                'email' => 'hacked@example.com',
            ])
            ->assertForbidden();

        $this->assertSame('Admin User', $user->fresh()->name);
    }
}
