<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First, drop foreign key constraints
        Schema::table('election_offices', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
        });
        
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
        });
        
        Schema::table('accredited_voters', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
        });
        
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
        });
        
        Schema::table('election_results', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
        });

        // Add soft deletes column
        Schema::table('elections', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Re-add foreign key constraints with cascade on delete
        Schema::table('election_offices', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections')->cascadeOnDelete();
        });
        
        Schema::table('candidates', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections')->cascadeOnDelete();
        });
        
        Schema::table('accredited_voters', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections')->cascadeOnDelete();
        });
        
        Schema::table('votes', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections')->cascadeOnDelete();
        });
        
        Schema::table('election_results', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections')->cascadeOnDelete();
        });
    }

    public function down()
    {
        // Drop foreign key constraints
        Schema::table('election_offices', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
        });
        
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
        });
        
        Schema::table('accredited_voters', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
        });
        
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
        });
        
        Schema::table('election_results', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
        });

        // Remove soft deletes column
        Schema::table('elections', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Re-add foreign key constraints
        Schema::table('election_offices', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections')->onDelete('cascade');
        });
        
        Schema::table('candidates', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections')->onDelete('cascade');
        });
        
        Schema::table('accredited_voters', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections')->onDelete('cascade');
        });
        
        Schema::table('votes', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections')->onDelete('cascade');
        });
        
        Schema::table('election_results', function (Blueprint $table) {
            $table->foreign('election_id')->references('id')->on('elections')->onDelete('cascade');
        });
    }
}; 