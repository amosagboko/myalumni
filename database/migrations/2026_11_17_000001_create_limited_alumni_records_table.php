<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('limited_alumni_records', function (Blueprint $table) {
            $table->id();
            $table->string('matric_number')->unique();
            $table->string('firstname');
            $table->string('surname');
            $table->unsignedSmallInteger('year_of_graduation')->default(2026);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('limited_alumni_records');
    }
};
