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
        // Get the subscription fee type
        $subscriptionFeeType = DB::table('fee_types')
            ->where('code', 'subscription')
            ->first();

        if ($subscriptionFeeType) {
            // Check if 2022 template already exists
            $exists = DB::table('fee_templates')
                ->where('fee_type_id', $subscriptionFeeType->id)
                ->where('graduation_year', 2022)
                ->exists();

            if (!$exists) {
                // Create subscription fee template for 2022
                DB::table('fee_templates')->insert([
                    'fee_type_id' => $subscriptionFeeType->id,
                    'name' => 'Annual Subscription Fee 2022',
                    'graduation_year' => 2022,
                    'amount' => 2000.00,  // ₦2,000 as specified
                    'description' => 'Annual subscription fee for alumni registration in 2022',
                    'is_active' => true,
                    'valid_from' => '2022-01-01',
                    'valid_until' => '2025-12-31',  // Valid until end of 2025
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get the subscription fee type
        $subscriptionFeeType = DB::table('fee_types')
            ->where('code', 'subscription')
            ->first();

        if ($subscriptionFeeType) {
            // Delete the subscription fee template for 2022
            DB::table('fee_templates')
                ->where('fee_type_id', $subscriptionFeeType->id)
                ->where('graduation_year', 2022)
                ->where('name', 'Annual Subscription Fee 2022')
                ->delete();
        }
    }
}; 