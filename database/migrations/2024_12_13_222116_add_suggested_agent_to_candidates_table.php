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
            $table->foreignId('suggested_agent_id')
                  ->nullable()
                  ->after('alumni_id')
                  ->constrained('alumni')
                  ->nullOnDelete();
            $table->enum('agent_status', ['pending', 'approved', 'rejected'])
                  ->nullable()
                  ->after('suggested_agent_id');
            $table->text('agent_rejection_reason')
                  ->nullable()
                  ->after('agent_status');
            // Rename existing agent_id to approved_agent_id for clarity
            $table->renameColumn('agent_id', 'approved_agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['suggested_agent_id']);
            $table->dropColumn(['suggested_agent_id', 'agent_status', 'agent_rejection_reason']);
            $table->renameColumn('approved_agent_id', 'agent_id');
        });
    }
}; 