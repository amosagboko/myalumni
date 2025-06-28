<?php

namespace App\Console\Commands;

use App\Models\Election;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class ExtendEoiPeriod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'election:extend-eoi {election_id} {days=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extend the EOI period for an election';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $electionId = $this->argument('election_id');
        $days = (int) $this->argument('days');

        $election = Election::find($electionId);

        if (!$election) {
            $this->error("Election with ID {$electionId} not found.");
            return 1;
        }

        $this->info("Election: {$election->title}");
        $this->info("Current EOI End: {$election->eoi_end?->format('M d, Y h:i A')}");
        $this->info("Status: {$election->status}");
        $this->info("Total Offices: {$election->offices->count()}");

        if (!$election->canExtendEoiPeriod()) {
            $this->error("Cannot extend EOI period for this election.");
            $this->info("Extension conditions not met.");
            return 1;
        }

        $extensionReasons = $election->getEoiExtensionReasons();
        $this->info("Extension Reasons:");
        foreach ($extensionReasons as $reason) {
            $this->line("  - {$reason}");
        }

        if ($election->isEoiPeriodActive()) {
            $this->info("Grace Period Extension:");
            $this->line("  - This extension applies to ALL offices regardless of candidate status");
            $this->line("  - Provides additional time for all potential candidates");
        }

        if ($election->hasOfficesWithNoCandidates()) {
            $officesWithNoCandidates = $election->getOfficesWithNoCandidates();
            $this->info("Offices with No Candidates:");
            foreach ($officesWithNoCandidates as $office) {
                $this->line("  - {$office->title}");
            }
        }

        $newEndDate = $election->eoi_end->addDays($days);
        $this->info("New EOI End Date: {$newEndDate->format('M d, Y h:i A')}");

        if ($this->confirm("Do you want to extend the EOI period by {$days} days?")) {
            if ($election->extendEoiPeriod($days)) {
                $this->info("EOI period has been successfully extended by {$days} days.");
                $this->info("New end date: {$newEndDate->format('M d, Y h:i A')}");
            } else {
                $this->error("Failed to extend EOI period.");
            }
        } else {
            $this->info("EOI period extension cancelled.");
        }

        return 0;
    }
} 