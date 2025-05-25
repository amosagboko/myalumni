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
        Schema::table('election_offices', function (Blueprint $table) {
            $table->foreignId('fee_type_id')->after('is_active')->constrained('fee_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('election_offices', function (Blueprint $table) {
            $table->dropForeign(['fee_type_id']);
            $table->dropColumn('fee_type_id');
        });
    }
}; 