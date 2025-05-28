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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('office_contest_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->references('id')->on('alumni')->onDelete('restrict');
            $table->foreignId('office_id')->constrained()->onDelete('restrict');
            $table->foreignId('transaction_id')->constrained()->onDelete('restrict');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('application_details')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            // Ensure one active application per alumni per office
            $table->unique(['alumni_id', 'office_id', 'status'], 'unique_alumni_office_status');
        });

        // Insert default offices
        DB::table('offices')->insert([
            [
                'name' => 'President',
                'code' => 'president',
                'description' => 'Alumni Association President',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Vice President',
                'code' => 'vice_president',
                'description' => 'Alumni Association Vice President',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Secretary General',
                'code' => 'secretary_general',
                'description' => 'Alumni Association Secretary General',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Financial Secretary',
                'code' => 'financial_secretary',
                'description' => 'Alumni Association Financial Secretary',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_contest_applications');
        Schema::dropIfExists('offices');
    }
}; 