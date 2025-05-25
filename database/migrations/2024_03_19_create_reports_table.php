<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('type')->default('general'); // general, academic, career, etc.
            $table->string('status')->default('draft'); // draft, published, archived
            $table->json('metadata')->nullable(); // For additional data like tags, categories
            $table->string('file_path')->nullable(); // For attached files
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}; 