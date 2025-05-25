<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeeType;

class FeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultFeeTypes = [
            [
                'name' => 'Registration Fee',
                'code' => 'registration',
                'description' => 'Initial registration fee for alumni membership',
                'is_system' => true
            ],
            [
                'name' => 'Development Levy',
                'code' => 'development_levy',
                'description' => 'Annual development levy for alumni association projects',
                'is_system' => true
            ],
            [
                'name' => 'Personal Data Processing Fee',
                'code' => 'data_processing',
                'description' => 'Fee for processing and maintaining alumni personal data',
                'is_system' => true
            ],
            [
                'name' => 'Subscription Registration Fee',
                'code' => 'subscription',
                'description' => 'Annual subscription fee for alumni registration',
                'is_system' => true
            ]
        ];

        foreach ($defaultFeeTypes as $feeType) {
            FeeType::firstOrCreate(
                ['code' => $feeType['code']],
                $feeType
            );
        }
    }
} 