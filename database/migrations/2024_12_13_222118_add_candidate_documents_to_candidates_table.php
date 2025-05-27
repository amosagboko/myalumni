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
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('passport')->nullable()->after('status');
            $table->text('manifesto')->nullable()->after('passport');
            $table->json('documents')->nullable()->after('manifesto');
            $table->text('rejection_reason')->nullable()->after('documents');
            $table->timestamp('screened_at')->nullable()->after('rejection_reason');
            $table->foreignId('screened_by')->nullable()->after('screened_at')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['screened_by']);
            $table->dropColumn([
                'passport',
                'manifesto',
                'documents',
                'rejection_reason',
                'screened_at',
                'screened_by'
            ]);
        });
    }
}; 