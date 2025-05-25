<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class AdministratorSeeder extends Seeder
{
    public function run()
    {
        // Create administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'uuid' => Str::uuid(),
                'name' => 'Test Administrator',
                'password' => bcrypt('password123'),
                'status' => 'active'
            ]
        );
        $admin->assignRole('administrator');
    }
} 