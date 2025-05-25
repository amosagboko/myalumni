<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Election;

class ListElections extends Command
{
    protected $signature = 'election:list';
    protected $description = 'List all elections with their status';

    public function handle()
    {
        $elections = Election::all();
        
        if ($elections->isEmpty()) {
            $this->error('No elections found in the database.');
            return 1;
        }

        $this->info("\nCurrent Elections:");
        $this->table(
            ['ID', 'Title', 'Status', 'Voting Start', 'Voting End', 'Accredited Voters'],
            $elections->map(function ($election) {
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'status' => $election->status,
                    'voting_start' => $election->voting_start?->format('Y-m-d H:i'),
                    'voting_end' => $election->voting_end?->format('Y-m-d H:i'),
                    'accredited_voters' => $election->getTotalAccreditedVoters()
                ];
            })
        );

        return 0;
    }
} 