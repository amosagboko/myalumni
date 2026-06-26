<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('transactions')
            ->whereIn('status', ['completed', 'success'])
            ->update(['status' => 'paid']);
    }

    public function down(): void
    {
        // Legacy statuses cannot be restored reliably.
    }
};
