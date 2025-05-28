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
        Schema::create('fee_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->constrained()->onDelete('restrict');
            $table->enum('rule_type', ['graduation_year_range', 'office_type', 'custom']);
            $table->json('rule_parameters');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Add index for faster lookups
            $table->index(['fee_type_id', 'rule_type', 'is_active']);
        });

        // Insert default rules for EOI fee
        DB::table('fee_rules')->insert([
            [
                'fee_type_id' => DB::table('fee_types')->where('code', 'eoi')->first()->id,
                'rule_type' => 'office_type',
                'rule_parameters' => json_encode([
                    'description' => 'Only applicable for office contest applications',
                    'requires_application' => 1,
                    'application_type' => 'eoi'
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fee_type_id' => DB::table('fee_types')->where('code', 'office_contest')->first()->id,
                'rule_type' => 'office_type',
                'rule_parameters' => json_encode([
                    'description' => 'Only applicable for office contest applications',
                    'requires_application' => 1,
                    'application_type' => 'office_contest'
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_rules');
    }
}; 