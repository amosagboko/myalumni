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
        Schema::table('transactions', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['user_id']);
            // Drop the user_id column
            $table->dropColumn('user_id');
            // Add the alumni_id column
            $table->foreignId('alumni_id')->after('id')->constrained('alumni')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['alumni_id']);
            // Drop the alumni_id column
            $table->dropColumn('alumni_id');
            // Add back the user_id column
            $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
        });
    }
}; 