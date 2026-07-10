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
        if (! Schema::hasTable('fee_templates') || Schema::hasColumn('fee_templates', 'name')) {
            return;
        }

        Schema::table('fee_templates', function (Blueprint $table) {
            $table->string('name', 255)->nullable()->after('fee_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_templates', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};