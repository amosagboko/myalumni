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
        // Create new table with desired structure
        Schema::create('fee_rules_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('alumni_categories')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Add unique constraint to prevent duplicate rules
            $table->unique(['fee_template_id', 'category_id'], 'unique_fee_template_category');
        });

        // Drop the old table
        Schema::dropIfExists('fee_rules');

        // Rename the new table to fee_rules
        Schema::rename('fee_rules_new', 'fee_rules');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create the old table structure
        Schema::create('fee_rules_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->constrained()->onDelete('restrict');
            $table->enum('rule_type', ['graduation_year_range', 'office_type', 'custom']);
            $table->json('rule_parameters');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Add index for faster lookups
            $table->index(['fee_type_id', 'rule_type', 'is_active']);
        });

        // Drop the new table
        Schema::dropIfExists('fee_rules');

        // Rename the old table back to fee_rules
        Schema::rename('fee_rules_old', 'fee_rules');
    }
}; 