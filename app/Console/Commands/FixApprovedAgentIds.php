<?php

namespace App\Console\Commands;

use App\Models\Alumni;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixApprovedAgentIds extends Command
{
    protected $signature = 'election:fix-agent-ids';
    protected $description = 'Normalize approved_agent_id values to users.id';

    public function handle(): int
    {
        $fixed = 0;

        Candidate::query()
            ->whereNotNull('approved_agent_id')
            ->each(function (Candidate $candidate) use (&$fixed) {
                if (User::whereKey($candidate->approved_agent_id)->exists()) {
                    return;
                }

                $alumni = Alumni::find($candidate->approved_agent_id);
                if (!$alumni?->user_id) {
                    $this->warn("Candidate #{$candidate->id}: could not resolve approved_agent_id {$candidate->approved_agent_id}");
                    return;
                }

                DB::table('candidates')
                    ->where('id', $candidate->id)
                    ->update(['approved_agent_id' => $alumni->user_id]);

                $fixed++;
            });

        $this->info("Normalized {$fixed} approved_agent_id value(s).");

        return self::SUCCESS;
    }
}
