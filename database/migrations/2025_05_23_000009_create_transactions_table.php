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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->references('id')->on('alumni')->onDelete('restrict');
            $table->foreignId('fee_template_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->boolean('is_test_mode')->default(false);
            $table->string('payment_reference', 100)->unique();
            $table->string('payment_method', 50)->default('card');
            $table->string('payment_provider', 50);
            $table->string('payment_provider_reference', 100)->nullable();
            $table->text('payment_link')->nullable();
            $table->json('payment_details')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for common queries
            $table->index(['alumni_id', 'status']);
            $table->index(['fee_template_id', 'status']);
            $table->index(['payment_reference']);
            $table->index(['payment_provider_reference']);
            $table->index(['created_at']);
            $table->index(['deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
}; 