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
            // Create subscription fee template for 2025
            DB::table('fee_templates')->insert([
                'fee_type_id' => $subscriptionFeeType->id,
                'name' => 'Annual Subscription Fee 2025',
                'graduation_year' => 2025,
                'amount' => 5000.00, // Base amount
                'description' => 'Annual subscription fee for alumni registration in 2025',
                'is_active' => true,
                'valid_from' => '2025-01-01',
                'valid_until' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ]);
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
            // Delete the subscription fee template
            DB::table('fee_templates')
                ->where('fee_type_id', $subscriptionFeeType->id)
                ->where('graduation_year', 2025)
                ->where('name', 'Annual Subscription Fee 2025')
                ->delete();
        }
    }
};
