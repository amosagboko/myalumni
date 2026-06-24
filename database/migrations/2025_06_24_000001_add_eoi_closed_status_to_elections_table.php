<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'eoi', 'eoi_closed', 'accreditation', 'voting', 'completed', 'archived') DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::table('elections')->where('status', 'eoi_closed')->update(['status' => 'draft']);

        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'eoi', 'accreditation', 'voting', 'completed', 'archived') DEFAULT 'draft'");
    }
};
