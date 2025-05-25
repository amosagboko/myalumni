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
        Schema::table('messages', function (Blueprint $table) {
            // Drop existing columns if they exist
            if (Schema::hasColumn('messages', 'sender_id')) {
                $table->dropForeign(['sender_id']);
                $table->dropColumn('sender_id');
            }
            if (Schema::hasColumn('messages', 'receiver_id')) {
                $table->dropForeign(['receiver_id']);
                $table->dropColumn('receiver_id');
            }
            if (Schema::hasColumn('messages', 'message')) {
                $table->dropColumn('message');
            }
            if (Schema::hasColumn('messages', 'read_at')) {
                $table->dropColumn('read_at');
            }

            // Add new columns
            $table->foreignId('sender_id')->after('id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->after('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('message')->after('receiver_id');
            $table->timestamp('read_at')->nullable()->after('message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Drop the new columns
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
            $table->dropColumn(['sender_id', 'receiver_id', 'message', 'read_at']);
        });
    }
}; 