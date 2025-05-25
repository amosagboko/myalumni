<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetElectionData extends Command
{
    protected $signature = 'election:reset';
    protected $description = 'Reset only election-related tables while preserving other data';

    public function handle()
    {
        if (!$this->confirm('This will reset all election data. Are you sure you want to continue?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            // Reset all votes
            DB::table('votes')->truncate();

            // Reset has_voted flag for all accredited voters
            DB::table('accredited_voters')->update(['has_voted' => false]);

            // Reset election statuses
            DB::table('elections')->update([
                'status' => 'draft',
                'updated_at' => now()
            ]);

            $this->info('Election data has been reset successfully.');
            $this->info('Next step: Run php artisan db:seed --class=TestDataSeeder to recreate election data.');

        } catch (\Exception $e) {
            $this->error('Error resetting election data: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
} 