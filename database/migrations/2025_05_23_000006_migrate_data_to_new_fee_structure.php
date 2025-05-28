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
        // Create default fee templates for each fee type
        $feeTypes = DB::table('fee_types')->get();
        $currentYear = date('Y');

        foreach ($feeTypes as $feeType) {
            // Create a template for the current year
            DB::table('fee_templates')->insert([
                'fee_type_id' => $feeType->id,
                'graduation_year' => $currentYear,
                'amount' => $this->getDefaultAmount($feeType->code),
                'description' => $this->getDefaultDescription($feeType->code, $currentYear),
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create a template for next year
            DB::table('fee_templates')->insert([
                'fee_type_id' => $feeType->id,
                'graduation_year' => $currentYear + 1,
                'amount' => $this->getDefaultAmount($feeType->code),
                'description' => $this->getDefaultDescription($feeType->code, $currentYear + 1),
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Get default amount for a fee type
     */
    private function getDefaultAmount(string $code): float
    {
        return match($code) {
            'registration' => 5000.00,
            'development_levy' => 2000.00,
            'eoi' => 1000.00,
            'office_contest' => 5000.00,
            default => 0.00
        };
    }

    /**
     * Get default description for a fee type
     */
    private function getDefaultDescription(string $code, int $year): string
    {
        return match($code) {
            'registration' => "Annual registration fee for {$year}",
            'development_levy' => "Annual development levy for {$year}",
            'eoi' => "Expression of interest fee for {$year}",
            'office_contest' => "Office contest fee for {$year}",
            default => "Fee for {$year}"
        };
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse seeding
    }
}; 