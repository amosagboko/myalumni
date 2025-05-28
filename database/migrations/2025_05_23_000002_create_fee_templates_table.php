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
        Schema::create('fee_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->constrained()->onDelete('restrict');
            $table->integer('graduation_year');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();

            // Ensure unique fee type per graduation year and valid_from date (without category_id)
            $table->unique(['fee_type_id', 'graduation_year', 'valid_from'], 'unique_fee_type_year_valid_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_templates');
    }
}; 