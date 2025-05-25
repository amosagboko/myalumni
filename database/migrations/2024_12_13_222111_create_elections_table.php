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
        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('eligibility_criteria')->nullable();
            $table->timestamp('accreditation_start');
            $table->timestamp('accreditation_end');
            $table->timestamp('voting_start');
            $table->timestamp('voting_end');
            $table->enum('status', ['draft', 'accreditation', 'voting', 'completed'])->default('draft');
            $table->decimal('screening_fee', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
