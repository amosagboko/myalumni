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
        if (! Schema::hasTable('fee_templates') || ! Schema::hasColumn('fee_templates', 'category_id')) {
            return;
        }

        // First, let's check if there are any foreign key constraints that might be using the unique constraint
        // We'll disable foreign key checks temporarily to safely modify the constraint
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // Drop the old unique constraint
            Schema::table('fee_templates', function (Blueprint $table) {
                $table->dropUnique('unique_fee_type_year_valid_from');
            });
            
            // Add new unique constraint that includes category_id
            Schema::table('fee_templates', function (Blueprint $table) {
                $table->unique(['fee_type_id', 'category_id', 'graduation_year', 'valid_from'], 'unique_fee_type_category_year_valid_from');
            });
            
        } catch (\Exception $e) {
            // If the constraint doesn't exist, that's fine - continue
            if (strpos($e->getMessage(), "doesn't exist") === false) {
                throw $e;
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            Schema::table('fee_templates', function (Blueprint $table) {
                // Drop the new unique constraint
                $table->dropUnique('unique_fee_type_category_year_valid_from');
                
                // Restore the old unique constraint
                $table->unique(['fee_type_id', 'graduation_year', 'valid_from'], 'unique_fee_type_year_valid_from');
            });
        } catch (\Exception $e) {
            // If the constraint doesn't exist, that's fine
            if (strpos($e->getMessage(), "doesn't exist") === false) {
                throw $e;
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};