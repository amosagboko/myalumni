<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->string('election_type')->default('general')->after('status');
            $table->foreignId('parent_election_id')->nullable()->after('cloned_from_election_id')
                ->constrained('elections')->nullOnDelete();
        });

        Schema::table('election_offices', function (Blueprint $table) {
            $table->foreignId('parent_office_id')->nullable()->after('winner_candidate_id')
                ->constrained('election_offices')->nullOnDelete();
            $table->string('by_election_mode')->nullable()->after('parent_office_id');
            $table->foreignId('by_election_id')->nullable()->after('by_election_mode')
                ->constrained('elections')->nullOnDelete();
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('parent_candidate_id')->nullable()->after('election_office_id')
                ->constrained('candidates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['parent_candidate_id']);
            $table->dropColumn('parent_candidate_id');
        });

        Schema::table('election_offices', function (Blueprint $table) {
            $table->dropForeign(['parent_office_id']);
            $table->dropForeign(['by_election_id']);
            $table->dropColumn(['parent_office_id', 'by_election_mode', 'by_election_id']);
        });

        Schema::table('elections', function (Blueprint $table) {
            $table->dropForeign(['parent_election_id']);
            $table->dropColumn(['election_type', 'parent_election_id']);
        });
    }
};
