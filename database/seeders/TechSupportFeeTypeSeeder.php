<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TechSupportFeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if tech_support fee type already exists
        $exists = DB::table('fee_types')
            ->where('code', 'tech_support')
            ->exists();

        if (!$exists) {
            // Add Tech Support Fee type
            DB::table('fee_types')->insert([
                'code' => 'tech_support',
                'name' => 'Tech Support Fee',
                'description' => 'Technology support and maintenance fee for alumni portal',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->command->info('Tech Support Fee type created successfully.');
        } else {
            $this->command->info('Tech Support Fee type already exists.');
        }
    }
} 