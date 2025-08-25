<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get fee types
        $registrationFeeType = DB::table('fee_types')->where('code', 'registration')->first();
        $developmentLevyType = DB::table('fee_types')->where('code', 'development_levy')->first();
        $dataProcessingType = DB::table('fee_types')->where('code', 'data_processing')->first();
        $techSupportType = DB::table('fee_types')->where('code', 'tech_support')->first();

        // Get categories
        $postgraduateCategory = DB::table('alumni_categories')->where('slug', 'postgraduate')->first();
        $undergraduateFullTimeCategory = DB::table('alumni_categories')->where('slug', 'undergraduate-full-time')->first();
        $undergraduatePartTimeCategory = DB::table('alumni_categories')->where('slug', 'undergraduate-part-time')->first();
        $diplomaCategory = DB::table('alumni_categories')->where('slug', 'diploma')->first();

        // Clear existing 2025 templates
        DB::table('fee_templates')
            ->where('graduation_year', 2025)
            ->whereIn('fee_type_id', [$registrationFeeType->id, $developmentLevyType->id, $dataProcessingType->id, $techSupportType->id])
            ->delete();

        // Create category-specific templates
        $templates = [
            // Postgraduate: Total ₦21,500
            ['fee_type_id' => $registrationFeeType->id, 'category_id' => $postgraduateCategory->id, 'amount' => 5000.00, 'description' => 'Registration Fee for Postgraduate (2025)'],
            ['fee_type_id' => $developmentLevyType->id, 'category_id' => $postgraduateCategory->id, 'amount' => 13000.00, 'description' => 'Development Levy for Postgraduate (2025)'],
            ['fee_type_id' => $dataProcessingType->id, 'category_id' => $postgraduateCategory->id, 'amount' => 2500.00, 'description' => 'Data Processing Fee for Postgraduate (2025)'],
            ['fee_type_id' => $techSupportType->id, 'category_id' => $postgraduateCategory->id, 'amount' => 1000.00, 'description' => 'Tech Support Fee for Postgraduate (2025)'],
            
            // Undergraduate Full-time: Total ₦16,200
            ['fee_type_id' => $registrationFeeType->id, 'category_id' => $undergraduateFullTimeCategory->id, 'amount' => 5000.00, 'description' => 'Registration Fee for Undergraduate (Full-time) (2025)'],
            ['fee_type_id' => $developmentLevyType->id, 'category_id' => $undergraduateFullTimeCategory->id, 'amount' => 7700.00, 'description' => 'Development Levy for Undergraduate (Full-time) (2025)'],
            ['fee_type_id' => $dataProcessingType->id, 'category_id' => $undergraduateFullTimeCategory->id, 'amount' => 2500.00, 'description' => 'Data Processing Fee for Undergraduate (Full-time) (2025)'],
            ['fee_type_id' => $techSupportType->id, 'category_id' => $undergraduateFullTimeCategory->id, 'amount' => 1000.00, 'description' => 'Tech Support Fee for Undergraduate (Full-time) (2025)'],
            
            // Undergraduate Part-time: Total ₦18,500
            ['fee_type_id' => $registrationFeeType->id, 'category_id' => $undergraduatePartTimeCategory->id, 'amount' => 5000.00, 'description' => 'Registration Fee for Undergraduate (Part-time) (2025)'],
            ['fee_type_id' => $developmentLevyType->id, 'category_id' => $undergraduatePartTimeCategory->id, 'amount' => 10000.00, 'description' => 'Development Levy for Undergraduate (Part-time) (2025)'],
            ['fee_type_id' => $dataProcessingType->id, 'category_id' => $undergraduatePartTimeCategory->id, 'amount' => 2500.00, 'description' => 'Data Processing Fee for Undergraduate (Part-time) (2025)'],
            ['fee_type_id' => $techSupportType->id, 'category_id' => $undergraduatePartTimeCategory->id, 'amount' => 1000.00, 'description' => 'Tech Support Fee for Undergraduate (Part-time) (2025)'],
            
            // Diploma: Total ₦13,500
            ['fee_type_id' => $registrationFeeType->id, 'category_id' => $diplomaCategory->id, 'amount' => 5000.00, 'description' => 'Registration Fee for Diploma (2025)'],
            ['fee_type_id' => $developmentLevyType->id, 'category_id' => $diplomaCategory->id, 'amount' => 5000.00, 'description' => 'Development Levy for Diploma (2025)'],
            ['fee_type_id' => $dataProcessingType->id, 'category_id' => $diplomaCategory->id, 'amount' => 2500.00, 'description' => 'Data Processing Fee for Diploma (2025)'],
            ['fee_type_id' => $techSupportType->id, 'category_id' => $diplomaCategory->id, 'amount' => 1000.00, 'description' => 'Tech Support Fee for Diploma (2025)']
        ];

        // Insert templates with common fields
        foreach ($templates as $template) {
            DB::table('fee_templates')->insert([
                'fee_type_id' => $template['fee_type_id'],
                'category_id' => $template['category_id'],
                'graduation_year' => 2025,
                'amount' => $template['amount'],
                'description' => $template['description'],
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down(): void
    {
        $feeTypeIds = DB::table('fee_types')
            ->whereIn('code', ['registration', 'development_levy', 'data_processing', 'tech_support'])
            ->pluck('id');

        DB::table('fee_templates')
            ->where('graduation_year', 2025)
            ->whereIn('fee_type_id', $feeTypeIds)
            ->delete();
    }
}; 