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
        Schema::table('fee_templates', function (Blueprint $table) {
            // Add category_id column if it doesn't exist
            if (!Schema::hasColumn('fee_templates', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('fee_type_id')->constrained('alumni_categories')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_templates', function (Blueprint $table) {
            if (Schema::hasColumn('fee_templates', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
        });
    }
}; 