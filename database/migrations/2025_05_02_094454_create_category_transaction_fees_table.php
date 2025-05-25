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
        Schema::create('category_transaction_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('alumni_categories')->onDelete('cascade');
            $table->enum('fee_type', ['registration', 'development_levy', 'data_processing']);
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure unique combination of category and fee type
            $table->unique(['category_id', 'fee_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_transaction_fees');
    }
};
