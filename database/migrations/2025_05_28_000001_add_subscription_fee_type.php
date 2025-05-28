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
        // Add subscription fee type
        DB::table('fee_types')->insert([
            'code' => 'subscription',
            'name' => 'Subscription Registration Fee',
            'description' => 'Annual subscription fee for alumni registration',
            'is_system' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('fee_types')->where('code', 'subscription')->delete();
    }
}; 