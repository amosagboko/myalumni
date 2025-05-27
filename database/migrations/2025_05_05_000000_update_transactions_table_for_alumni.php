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
        // Remove orphaned transactions (if any) before adding the foreign key constraint
        DB::table('transactions')->whereNotIn('user_id', function($query) {
            $query->select('id')->from('users');
        })->delete();
        
        Schema::table('transactions', function (Blueprint $table) {
            // Check if the foreign key (or column) exists before dropping it
            if (Schema::hasColumn('transactions', 'user_id')) {
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Foreign key (or column) does not exist, continue
                }
                $table->dropColumn('user_id');
            }
            // Add the alumni_id column (and its FK constraint)
            $table->foreignId('alumni_id')->after('id')->constrained('alumni')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Check if the foreign key (or column) exists before dropping it
            if (Schema::hasColumn('transactions', 'alumni_id')) {
                try {
                    $table->dropForeign(['alumni_id']);
                } catch (\Exception $e) {
                    // Foreign key (or column) does not exist, continue
                }
                $table->dropColumn('alumni_id');
            }
            // Re-add the user_id column (and its FK constraint) only if it does not already exist
            if (!Schema::hasColumn('transactions', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
            }
        });
    }
}; 