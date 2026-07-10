<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('fee_templates') || ! Schema::hasColumn('fee_templates', 'category_id')) {
            return;
        }

        Schema::table('fee_templates', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique('unique_fee_type_year_valid_from');
            
            // Add new unique constraint that includes category_id
            $table->unique(['fee_type_id', 'category_id', 'graduation_year', 'valid_from'], 'unique_fee_type_category_year_valid_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_templates', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('unique_fee_type_category_year_valid_from');
            
            // Restore the old unique constraint
            $table->unique(['fee_type_id', 'graduation_year', 'valid_from'], 'unique_fee_type_year_valid_from');
        });
    }
}; 