<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // First, create all roles and permissions
        $this->call([
            RoleSeeder::class,
            RolePermissionSeeder::class,
            FeeTemplatePermissionSeeder::class,
            AlumniYearSeeder::class,
            AlumniCategorySeeder::class
        ]);

        // Seed payment system data
        $this->call(PaymentSystemSeeder::class);

        // Create test user and assign role
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->assignRole('administrator');
    }
}