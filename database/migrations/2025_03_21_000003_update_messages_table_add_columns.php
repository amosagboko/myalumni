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

            // Add message type to distinguish between text, image, file etc.
            $table->string('type')->default('text')->after('message');
            
            // Add attachment path for media messages
            $table->string('attachment')->nullable()->after('type');
            
            // Add message status (sent, delivered, read)
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent')->after('attachment');
            
            // Add soft deletes for message deletion
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('messages', 'attachment')) {
                $table->dropColumn('attachment');
            }
            if (Schema::hasColumn('messages', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('messages', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
}; 