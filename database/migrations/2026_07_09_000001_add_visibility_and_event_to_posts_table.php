<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('visibility', 20)->default('connections')->after('status');
            $table->foreignId('event_id')->nullable()->after('visibility')->constrained('events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn(['visibility', 'event_id']);
        });
    }
};
