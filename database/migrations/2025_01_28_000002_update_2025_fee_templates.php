<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the fee types
        $registrationFeeType = DB::table('fee_types')->where('code', 'registration')->first();
        $developmentLevyType = DB::table('fee_types')->where('code', 'development_levy')->first();
        $dataProcessingType = DB::table('fee_types')->where('code', 'data_processing')->first();
        $techSupportType = DB::table('fee_types')->where('code', 'tech_support')->first();

        if (!$registrationFeeType || !$developmentLevyType || !$dataProcessingType || !$techSupportType) {
            throw new \Exception('Required fee types not found. Please ensure all fee types exist before running this migration.');
        }

        // Clear existing 2025 fee templates for these fee types
        DB::table('fee_templates')
            ->where('graduation_year', 2025)
            ->whereIn('fee_type_id', [
                $registrationFeeType->id,
                $developmentLevyType->id,
                $dataProcessingType->id,
                $techSupportType->id
            ])
            ->delete();

        // Create basic fee templates for 2025+ alumni (without category filtering for now)
        $feeTemplates = [
            [
                'fee_type_id' => $registrationFeeType->id,
                'graduation_year' => 2025,
                'amount' => 5000.00,
                'description' => 'Registration Fee (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $developmentLevyType->id,
                'graduation_year' => 2025,
                'amount' => 10000.00,
                'description' => 'Development Levy (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $dataProcessingType->id,
                'graduation_year' => 2025,
                'amount' => 2500.00,
                'description' => 'Data Processing Fee (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $techSupportType->id,
                'graduation_year' => 2025,
                'amount' => 1000.00,
                'description' => 'Tech Support Fee (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insert all fee templates
        foreach ($feeTemplates as $template) {
            DB::table('fee_templates')->insert($template);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove all 2025 fee templates for these fee types
        $feeTypeIds = DB::table('fee_types')
            ->whereIn('code', ['registration', 'development_levy', 'data_processing', 'tech_support'])
            ->pluck('id');

        DB::table('fee_templates')
            ->where('graduation_year', 2025)
            ->whereIn('fee_type_id', $feeTypeIds)
            ->delete();
    }
}; 