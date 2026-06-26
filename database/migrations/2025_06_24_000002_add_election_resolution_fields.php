<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'eoi', 'eoi_closed', 'accreditation', 'voting', 'incomplete', 'completed', 'archived') DEFAULT 'draft'");

        Schema::table('election_offices', function (Blueprint $table) {
            $table->string('resolution_status')->nullable()->after('is_active');
            $table->foreignId('winner_candidate_id')->nullable()->after('resolution_status')->constrained('candidates')->nullOnDelete();
        });

        Schema::table('election_results', function (Blueprint $table) {
            $table->boolean('is_tied')->default(false)->after('is_winner');
        });
    }

    public function down(): void
    {
        Schema::table('election_results', function (Blueprint $table) {
            $table->dropColumn('is_tied');
        });

        Schema::table('election_offices', function (Blueprint $table) {
            $table->dropForeign(['winner_candidate_id']);
            $table->dropColumn(['resolution_status', 'winner_candidate_id']);
        });

        DB::table('elections')->where('status', 'incomplete')->update(['status' => 'completed']);

        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'eoi', 'eoi_closed', 'accreditation', 'voting', 'completed', 'archived') DEFAULT 'draft'");
    }
};
