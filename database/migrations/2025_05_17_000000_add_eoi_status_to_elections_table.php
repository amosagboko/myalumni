<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the enum to include 'eoi'
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'eoi', 'accreditation', 'voting', 'completed') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'eoi' from the enum
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'accreditation', 'voting', 'completed') DEFAULT 'draft'");
    }
}; 