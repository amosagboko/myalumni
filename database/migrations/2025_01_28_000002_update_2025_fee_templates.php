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

        // Get alumni categories
        $postgraduateCategory = DB::table('alumni_categories')->where('slug', 'postgraduate')->first();
        $undergraduateFullTimeCategory = DB::table('alumni_categories')->where('slug', 'undergraduate-full-time')->first();
        $undergraduatePartTimeCategory = DB::table('alumni_categories')->where('slug', 'undergraduate-part-time')->first();
        $diplomaCategory = DB::table('alumni_categories')->where('slug', 'diploma')->first();

        if (!$postgraduateCategory || !$undergraduateFullTimeCategory || !$undergraduatePartTimeCategory || !$diplomaCategory) {
            throw new \Exception('Required alumni categories not found. Please ensure all categories exist before running this migration.');
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

        // Create fee templates for 2025+ alumni with correct amounts
        $feeTemplates = [
            // Postgraduate fees
            [
                'fee_type_id' => $registrationFeeType->id,
                'category_id' => $postgraduateCategory->id,
                'graduation_year' => 2025,
                'amount' => 5000.00,
                'description' => 'Registration Fee for Postgraduate (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $developmentLevyType->id,
                'category_id' => $postgraduateCategory->id,
                'graduation_year' => 2025,
                'amount' => 13000.00,
                'description' => 'Development Levy for Postgraduate (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $dataProcessingType->id,
                'category_id' => $postgraduateCategory->id,
                'graduation_year' => 2025,
                'amount' => 2500.00,
                'description' => 'Data Processing Fee for Postgraduate (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $techSupportType->id,
                'category_id' => $postgraduateCategory->id,
                'graduation_year' => 2025,
                'amount' => 1000.00,
                'description' => 'Tech Support Fee for Postgraduate (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Undergraduate (Full-time) fees
            [
                'fee_type_id' => $registrationFeeType->id,
                'category_id' => $undergraduateFullTimeCategory->id,
                'graduation_year' => 2025,
                'amount' => 5000.00,
                'description' => 'Registration Fee for Undergraduate (Full-time) (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $developmentLevyType->id,
                'category_id' => $undergraduateFullTimeCategory->id,
                'graduation_year' => 2025,
                'amount' => 7700.00,
                'description' => 'Development Levy for Undergraduate (Full-time) (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $dataProcessingType->id,
                'category_id' => $undergraduateFullTimeCategory->id,
                'graduation_year' => 2025,
                'amount' => 2500.00,
                'description' => 'Data Processing Fee for Undergraduate (Full-time) (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $techSupportType->id,
                'category_id' => $undergraduateFullTimeCategory->id,
                'graduation_year' => 2025,
                'amount' => 1000.00,
                'description' => 'Tech Support Fee for Undergraduate (Full-time) (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Undergraduate (Part-time) fees
            [
                'fee_type_id' => $registrationFeeType->id,
                'category_id' => $undergraduatePartTimeCategory->id,
                'graduation_year' => 2025,
                'amount' => 5000.00,
                'description' => 'Registration Fee for Undergraduate (Part-time) (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $developmentLevyType->id,
                'category_id' => $undergraduatePartTimeCategory->id,
                'graduation_year' => 2025,
                'amount' => 10000.00,
                'description' => 'Development Levy for Undergraduate (Part-time) (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $dataProcessingType->id,
                'category_id' => $undergraduatePartTimeCategory->id,
                'graduation_year' => 2025,
                'amount' => 2500.00,
                'description' => 'Data Processing Fee for Undergraduate (Part-time) (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $techSupportType->id,
                'category_id' => $undergraduatePartTimeCategory->id,
                'graduation_year' => 2025,
                'amount' => 1000.00,
                'description' => 'Tech Support Fee for Undergraduate (Part-time) (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Diploma fees
            [
                'fee_type_id' => $registrationFeeType->id,
                'category_id' => $diplomaCategory->id,
                'graduation_year' => 2025,
                'amount' => 5000.00,
                'description' => 'Registration Fee for Diploma (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $developmentLevyType->id,
                'category_id' => $diplomaCategory->id,
                'graduation_year' => 2025,
                'amount' => 5000.00,
                'description' => 'Development Levy for Diploma (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $dataProcessingType->id,
                'category_id' => $diplomaCategory->id,
                'graduation_year' => 2025,
                'amount' => 2500.00,
                'description' => 'Data Processing Fee for Diploma (2025)',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => $techSupportType->id,
                'category_id' => $diplomaCategory->id,
                'graduation_year' => 2025,
                'amount' => 1000.00,
                'description' => 'Tech Support Fee for Diploma (2025)',
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