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
        // First, add the new column to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('category_transaction_fee_id')->nullable()->after('fee_template_id')->constrained('category_transaction_fees')->nullOnDelete();
        });

        // Drop the old foreign key and column
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['fee_template_id']);
            $table->dropColumn('fee_template_id');
        });

        // Finally, drop the fee_templates table
        Schema::dropIfExists('fee_templates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the fee_templates table
        Schema::create('fee_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_year_id')->constrained();
            $table->string('name');
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add back the old column to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('fee_template_id')->after('user_id')->constrained();
        });

        // Drop the new column
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['category_transaction_fee_id']);
            $table->dropColumn('category_transaction_fee_id');
        });
    }
};
