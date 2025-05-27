<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accredited_voters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alumni_id')->constrained('alumni')->cascadeOnDelete();
            $table->boolean('has_voted')->default(false);
            $table->timestamp('accredited_at')->nullable();
            $table->timestamp('voted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accredited_voters');
    }
}; 