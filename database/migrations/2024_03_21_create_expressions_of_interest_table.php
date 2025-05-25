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
        Schema::create('expressions_of_interest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_office_id')->constrained('election_offices')->onDelete('cascade');
            $table->foreignId('alumni_id')->constrained('alumni')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Ensure an alumni can only express interest once per office in an election
            $table->unique(['election_id', 'election_office_id', 'alumni_id'], 'eoi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expressions_of_interest');
    }
}; 