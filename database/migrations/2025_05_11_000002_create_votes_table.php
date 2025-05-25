<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accredited_voter_id')->constrained('accredited_voters')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Add unique constraint to ensure one vote per voter per office
            $table->unique(['election_id', 'election_office_id', 'accredited_voter_id'], 'unique_vote_per_office');
        });
    }

    public function down()
    {
        Schema::dropIfExists('votes');
    }
}; 