<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add clearance flags to alumni
        Schema::table('alumni', function (Blueprint $table) {
            if (!Schema::hasColumn('alumni', 'student_affairs_cleared')) {
                $table->boolean('student_affairs_cleared')->default(false)->after('category_id');
            }
            if (!Schema::hasColumn('alumni', 'academic_affairs_cleared')) {
                $table->boolean('academic_affairs_cleared')->default(false)->after('student_affairs_cleared');
            }
        });

        // Create clearance_logs table
        if (!Schema::hasTable('clearance_logs')) {
            Schema::create('clearance_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alumni_id')->constrained('alumni')->cascadeOnDelete();
                $table->enum('division', ['student_affairs', 'academic_affairs']);
                $table->foreignId('actor_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('actor_role')->nullable();
                $table->boolean('old_value')->default(false);
                $table->boolean('new_value')->default(false);
                $table->text('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clearance_logs')) {
            Schema::dropIfExists('clearance_logs');
        }
        Schema::table('alumni', function (Blueprint $table) {
            if (Schema::hasColumn('alumni', 'academic_affairs_cleared')) {
                $table->dropColumn('academic_affairs_cleared');
            }
            if (Schema::hasColumn('alumni', 'student_affairs_cleared')) {
                $table->dropColumn('student_affairs_cleared');
            }
        });
    }
};
