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
        // Drop the old foreign key and column if they exist
        if (Schema::hasColumn('transactions', 'category_transaction_fee_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['category_transaction_fee_id']);
                $table->dropColumn('category_transaction_fee_id');
            });
        }

        // Drop the old fees table if it exists
        if (Schema::hasTable('category_transaction_fees')) {
            Schema::dropIfExists('category_transaction_fees');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed; we are not restoring the old system.
    }
}; 