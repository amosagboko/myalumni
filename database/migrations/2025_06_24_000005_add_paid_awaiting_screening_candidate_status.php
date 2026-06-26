<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE candidates MODIFY COLUMN status ENUM('pending', 'paid_awaiting_screening', 'approved', 'rejected') NOT NULL DEFAULT 'pending'"
        );

        DB::table('candidates')
            ->where('has_paid_screening_fee', true)
            ->where('status', 'pending')
            ->update(['status' => 'paid_awaiting_screening']);
    }

    public function down(): void
    {
        DB::table('candidates')
            ->where('status', 'paid_awaiting_screening')
            ->update(['status' => 'pending']);

        DB::statement(
            "ALTER TABLE candidates MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'"
        );
    }
};
