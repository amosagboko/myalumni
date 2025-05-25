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
            $table->integer('term_duration')->default(1)->after('max_terms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('election_offices', function (Blueprint $table) {
            $table->dropColumn('term_duration');
        });
    }
};
