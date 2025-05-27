<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('matric_number')->unique();
            $table->string('programme');
            $table->string('department');
            $table->string('faculty');
            $table->integer('year_of_graduation');
            $table->date('date_of_birth');
            $table->string('state');
            $table->string('lga');
            $table->integer('year_of_entry');
            $table->string('gender');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alumni');
    }
}; 