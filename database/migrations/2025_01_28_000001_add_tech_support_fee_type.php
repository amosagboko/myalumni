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
        if (! Schema::hasTable('fee_types') || DB::table('fee_types')->where('code', 'tech_support')->exists()) {
            return;
        }

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('fee_types')->where('code', 'tech_support')->delete();
    }
}; 