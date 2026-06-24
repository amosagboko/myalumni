<?php

namespace App\Console\Commands;

use App\Models\Election;
use Illuminate\Console\Command;

class PrepareElectionCycles extends Command
{
    protected $signature = 'election:prepare-cycles {--archive-completed : Archive all completed elections}';
    protected $description = 'Backfill election years and optionally archive completed elections for cycle rollover';

    public function handle(): int
    {
        $updated = 0;

        Election::query()->whereNull('election_year')->each(function (Election $election) use (&$updated) {
            $election->update([
                'election_year' => (int) $election->created_at->format('Y'),
            ]);
            $updated++;
        });

        $this->info("Backfilled election_year on {$updated} election(s).");

        Election::query()
            ->where('status', 'completed')
            ->update(['is_active' => false]);

        $this->info('Set is_active = false on all completed elections.');

        if ($this->option('archive-completed')) {
            $count = Election::completedUnarchived()->update([
                'status' => 'archived',
                'archived_at' => now(),
                'is_active' => false,
            ]);
            $this->info("Archived {$count} completed election(s).");
        }

        $active = Election::operational()->where('is_active', true)->count();
        if ($active === 0) {
            $latest = Election::operational()->orderByDesc('id')->first();
            if ($latest) {
                $latest->update(['is_active' => true]);
                $this->info("Marked election #{$latest->id} as the active cycle.");
            }
        }

        return self::SUCCESS;
    }
}
