<?php

namespace Database\Seeders;

use App\Models\OnboardingSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OnboardingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default onboarding setting (enabled by default)
        OnboardingSetting::firstOrCreate(
            ['id' => 1],
            [
                'is_onboarding_enabled' => true,
                'closure_reason' => null,
                'closed_at' => null,
                'reopened_at' => null,
                'closed_by' => null,
                'reopened_by' => null
            ]
        );

        $this->command->info('Onboarding settings initialized successfully.');
    }
} 