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
        // First, drop the foreign key constraint
        Schema::table('category_transaction_fees', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        // Then add the new column
        Schema::table('category_transaction_fees', function (Blueprint $table) {
            $table->foreignId('alumni_year_id')->after('category_id')->constrained('alumni_years')->onDelete('cascade');
        });

        // Now we can safely modify the unique constraint
        DB::statement('ALTER TABLE category_transaction_fees DROP INDEX category_transaction_fees_category_id_fee_type_unique');
        DB::statement('ALTER TABLE category_transaction_fees ADD UNIQUE INDEX category_transaction_fees_category_id_fee_type_year_unique (category_id, fee_type, alumni_year_id)');

        // Finally, recreate the foreign key constraint
        Schema::table('category_transaction_fees', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('alumni_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, drop the foreign key constraint
        Schema::table('category_transaction_fees', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        // Then modify the unique constraint
        DB::statement('ALTER TABLE category_transaction_fees DROP INDEX category_transaction_fees_category_id_fee_type_year_unique');
        DB::statement('ALTER TABLE category_transaction_fees ADD UNIQUE INDEX category_transaction_fees_category_id_fee_type_unique (category_id, fee_type)');

        // Drop the alumni_year_id column
        Schema::table('category_transaction_fees', function (Blueprint $table) {
            $table->dropColumn('alumni_year_id');
        });

        // Finally, recreate the foreign key constraint
        Schema::table('category_transaction_fees', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('alumni_categories')->onDelete('cascade');
        });
    }
};
