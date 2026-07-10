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

            if (Schema::hasTable('alumni_categories')) {
                $table->foreignId('category_id')->nullable()->constrained('alumni_categories')->nullOnDelete();
            }

            $table->string('name', 255)->nullable();
            $table->string('fee_purpose', 32)->nullable();
            $table->integer('graduation_year');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();

            if (Schema::hasTable('alumni_categories')) {
                $table->unique(
                    ['fee_type_id', 'category_id', 'graduation_year', 'valid_from'],
                    'unique_fee_type_category_year_valid_from'
                );
            } else {
                $table->unique(['fee_type_id', 'graduation_year', 'valid_from'], 'unique_fee_type_year_valid_from');
            }

            $table->index('fee_purpose');
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