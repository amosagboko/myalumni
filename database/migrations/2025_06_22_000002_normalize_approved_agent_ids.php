<?php

use App\Models\Alumni;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Candidate::query()
            ->whereNotNull('approved_agent_id')
            ->each(function (Candidate $candidate) {
                if (User::whereKey($candidate->approved_agent_id)->exists()) {
                    return;
                }

                $alumni = Alumni::find($candidate->approved_agent_id);
                if ($alumni?->user_id) {
                    DB::table('candidates')
                        ->where('id', $candidate->id)
                        ->update(['approved_agent_id' => $alumni->user_id]);
                }
            });
    }

    public function down(): void
    {
        // Data correction is not safely reversible.
    }
};
