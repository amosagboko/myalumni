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
        Schema::table('events', function (Blueprint $table) {
            $table->enum('type', ['connect', 'event', 'opportunity'])->default('event')->after('user_id');
            $table->text('description')->nullable()->after('venue');
            $table->string('image')->nullable()->after('description');
            $table->string('link')->nullable()->after('image');
            $table->boolean('is_published')->default(true)->after('link');
            $table->integer('order')->nullable()->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'image', 'link', 'is_published', 'order']);
        });
    }
};
